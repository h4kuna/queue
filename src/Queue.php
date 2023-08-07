<?php declare(strict_types=1);

namespace h4kuna\Queue;

use DateTimeImmutable;
use h4kuna\Memoize\MemoryStorage;
use h4kuna\Queue\SystemV\MsgInterface;

final class Queue
{
	use MemoryStorage;

	public function __construct(
		private Backup $backup,
		private MsgInterface $msg,
	)
	{
		$this->restore();
	}


	public function remove(): bool
	{
		return $this->msg->remove();
	}


	public function name(): string
	{
		return $this->msg->name();
	}


	/**
	 * @param array<MsgInterface::INFO_SETUP_*, int> $data
	 */
	public function setup(array $data): bool
	{
		return $this->msg->setup($data);
	}


	/**
	 * @return array<string, mixed>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function information(): array
	{
		$info = $this->msg->info();
		$extends = [
			MsgInterface::INFO_SETUP_MODE => Linux::permissionInToText($info[MsgInterface::INFO_SETUP_MODE]),
			MsgInterface::INFO_CREATE_TIME => self::createDateTime($info[MsgInterface::INFO_CREATE_TIME]),
			MsgInterface::INFO_SEND_TIME => self::createDateTime($info[MsgInterface::INFO_SEND_TIME]),
			MsgInterface::INFO_RECEIVE_TIME => self::createDateTime($info[MsgInterface::INFO_RECEIVE_TIME]),
			MsgInterface::INFO_SETUP_BYTES => $info[MsgInterface::INFO_SETUP_BYTES],
			MsgInterface::INFO_COUNT => $info[MsgInterface::INFO_COUNT],
			MsgInterface::INFO_LAST_RECEIVE_PID => $info[MsgInterface::INFO_LAST_RECEIVE_PID],
			MsgInterface::INFO_LAST_SEND_PID => $info[MsgInterface::INFO_LAST_SEND_PID],
		];

		[
			'user' => $extends[MsgInterface::INFO_SETUP_UID],
			'group' => $extends[MsgInterface::INFO_SETUP_GID],
		] = Linux::userGroupToText($info[MsgInterface::INFO_SETUP_UID], $info[MsgInterface::INFO_SETUP_GID]);

		return $extends;
	}


	private static function createDateTime(int $timestamp): ?DateTimeImmutable
	{
		if ($timestamp === 0) {
			return null;
		}

		return new DateTimeImmutable("@$timestamp");
	}


	/**
	 * If you want prepare queue with master process, let's try to use remove()
	 */
	public function flush(int $type = Config::TYPE_ALL): void
	{
		$consumer = $this->consumer();
		while ($consumer->tryReceive($type) !== null) {
		}
	}


	public function consumer(): Consumer
	{
		return $this->memoize(__METHOD__, function (): Consumer {
			return new Consumer($this->backup, $this->msg);
		});
	}


	public function producer(): Producer
	{
		return $this->memoize(__METHOD__, function (): Producer {
			return new Producer($this->backup, $this->msg);
		});
	}


	public function restore(): void
	{
		if ($this->backup->needRestore()) {
			$this->backup->restore($this->producer());
		}
	}

}
