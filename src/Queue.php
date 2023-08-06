<?php declare(strict_types=1);

namespace h4kuna\Queue;

use DateTimeImmutable;
use h4kuna\Memoize\MemoryStorage;
use SysvMessageQueue;

final class Queue
{
	use MemoryStorage;

	public const MAX_MESSAGE_SIZE = 256; // bytes, max is by system, observed 8192
	public const INFO_SETUP_UID = 'msg_perm.uid';
	public const INFO_SETUP_GID = 'msg_perm.gid';
	public const INFO_SETUP_MODE = 'msg_perm.mode';
	public const INFO_CREATE_TIME = 'msg_ctime';
	public const INFO_SEND_TIME = 'msg_stime';
	public const INFO_RECEIVE_TIME = 'msg_rtime';
	public const INFO_COUNT = 'msg_qnum';
	public const INFO_SETUP_BYTES = 'msg_qbytes';
	public const INFO_LAST_SEND_PID = 'msg_lspid';
	public const INFO_LAST_RECEIVE_PID = 'msg_lrpid';


	public function __construct(
		private string $filename,
		private string $projectId,
		private Backup $backup,
		private int $permission,
		private int $maxMessageSize = self::MAX_MESSAGE_SIZE,
	)
	{
	}


	public function remove(): bool
	{
		return msg_remove_queue($this->resource());
	}


	public function resource(): SysvMessageQueue
	{
		return $this->createResource();
	}


	private function createResource(): SysvMessageQueue
	{
		$key = $this->queueKey();
		$exists = msg_queue_exists($key);
		$queue = msg_get_queue($key, $this->permission);

		if ($queue === false) {
			throw new Exceptions\CreateQueueException(sprintf('Queue "%s" failed to create.', $this->name()));
		}

		if ($exists && ($perm = $this->queuePermission($queue)) !== $this->permission) {
			throw new Exceptions\CreateQueueException(sprintf('Queue "%s" already exists with permissions "%s" and you require "%s". %s',
				$this->name(), $perm, $this->permission, $this->helpHowRemove($perm)));
		}

		return $queue;
	}


	private function queueKey(): int
	{
		$key = ftok($this->filename, $this->projectId);
		if ($key === -1) {
			throw new Exceptions\CreateQueueException(sprintf('Queue "%s" failed to create. Probably file does not exists "%s" or project id "%s" is not valid.',
				$this->name(), $this->filename, $this->projectId));
		}

		return $key;
	}


	public function name(): string
	{
		return basename($this->filename);
	}


	private function queuePermission(SysvMessageQueue $queue): int
	{
		$stats = msg_stat_queue($queue);
		if (!is_array($stats)) {
			throw new Exceptions\CreateQueueException(sprintf('Bad initialize message queue. %s',
				$this->helpHowRemove($this->permission)));
		}

		return $stats[self::INFO_SETUP_MODE];
	}


	private function helpHowRemove(int $permission): string
	{
		return sprintf("Remove exist queue by cli: php -r 'msg_remove_queue(msg_get_queue(%s, %s));'", $this->queueKey(), $permission);
	}


	/**
	 * @param array<self::INFO_SETUP_*, int> $data
	 * @return bool
	 */
	public function setup(array $data): bool
	{
		$structure = [self::INFO_SETUP_UID, self::INFO_SETUP_GID, self::INFO_SETUP_MODE, self::INFO_SETUP_BYTES];

		return msg_set_queue($this->resource(), array_intersect_key($data, array_fill_keys($structure, true)));
	}


	/**
	 * @return array<string, mixed>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function information()
	{
		$info = $this->info();
		$extends = [
			self::INFO_SETUP_MODE => Linux::permissionInToText($info[self::INFO_SETUP_MODE]),
			self::INFO_CREATE_TIME => self::createDateTime($info[self::INFO_CREATE_TIME]),
			self::INFO_SEND_TIME => self::createDateTime($info[self::INFO_SEND_TIME]),
			self::INFO_RECEIVE_TIME => self::createDateTime($info[self::INFO_RECEIVE_TIME]),
			self::INFO_SETUP_BYTES => $info[self::INFO_SETUP_BYTES],
			self::INFO_COUNT => $info[self::INFO_COUNT],
			self::INFO_LAST_RECEIVE_PID => $info[self::INFO_LAST_RECEIVE_PID],
			self::INFO_LAST_SEND_PID => $info[self::INFO_LAST_SEND_PID],
		];

		[
			'user' => $extends[self::INFO_SETUP_UID],
			'group' => $extends[self::INFO_SETUP_GID],
		] = Linux::userGroupToText($info[self::INFO_SETUP_UID], $info[self::INFO_SETUP_GID]);

		return $extends;
	}


	/**
	 * @return array<self::INFO_*, int>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function info(): array
	{
		$info = msg_stat_queue($this->resource());
		if ($info === false) {
			throw new Exceptions\QueueInfoIsUnavailableException;
		}

		return $info;
	}


	private static function createDateTime(int $timestamp): ?DateTimeImmutable
	{
		if ($timestamp === 0) {
			return null;
		}

		return new DateTimeImmutable("@$timestamp");
	}


	public function messageSizeBytes(): int
	{
		return $this->maxMessageSize;
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
			return new Consumer($this, $this->backup);
		});
	}


	public function producer(): Producer
	{
		return $this->memoize(__METHOD__, function (): Producer {
			return new Producer($this, $this->backup);
		});
	}

}
