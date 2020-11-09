<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Memoize\MemoryStorage;
use h4kuna\Queue\Exceptions;

final class Queue
{
	use MemoryStorage;

	public const MAX_MESSAGE_SIZE = 256; // bytes, max is by system, observed 8192
	public const INFO_UID = 'msg_perm.uid';
	public const INFO_GID = 'msg_perm.gid';
	public const INFO_MODE = 'msg_perm.mode';
	public const INFO_CREATE_TIME = 'msg_ctime';
	public const INFO_SEND_TIME = 'msg_stime';
	public const INFO_RECEIVE_TIME = 'msg_rtime';
	public const INFO_COUNT = 'msg_qnum';
	public const INFO_BYTES = 'msg_qbytes';
	public const INFO_LAST_SEND_PID = 'msg_lspid';
	public const INFO_LAST_RECEIVE_PID = 'msg_lrpid';

	/** @var string */
	private $filename;

	/** @var int */
	private $permission;

	/** @var int */
	private $maxMessageSize;


	public function __construct(string $filename, int $permission, int $maxMessageSize = self::MAX_MESSAGE_SIZE)
	{
		$this->filename = $filename;
		$this->permission = $permission;
		$this->maxMessageSize = $maxMessageSize;
	}


	public function filename(): string
	{
		return $this->filename;
	}


	public function resource()
	{
		return $this->createResource();
	}


	public function remove(): bool
	{
		throw new \RuntimeException('not implemented');
		return msg_remove_queue($this->resource());
	}


	public function setup(array $data): bool
	{
		throw new \RuntimeException('not implemented');
		$structure = [self::INFO_UID, self::INFO_GID, self::INFO_MODE, self::INFO_BYTES];

		return msg_set_queue($this->resource(), array_intersect_key($data, array_fill_keys($structure, true)));
	}


	/**
	 * @return array<string, mixed>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function info(): array
	{
		throw new \RuntimeException('not implemented');
		$info = msg_stat_queue($this->resource());
		if ($info === false) {
			throw new Exceptions\QueueInfoIsUnavailableException;
		}

		return $info;
	}


	/**
	 * @return array<string, mixed>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function information()
	{
		$info = $this->info();
		$extends = [
			self::INFO_MODE => Linux::permissionInToText($info[self::INFO_MODE]),
			self::INFO_CREATE_TIME => self::createDateTime($info[self::INFO_CREATE_TIME]),
			self::INFO_SEND_TIME => self::createDateTime($info[self::INFO_SEND_TIME]),
			self::INFO_RECEIVE_TIME => self::createDateTime($info[self::INFO_RECEIVE_TIME]),
			self::INFO_BYTES => $info[self::INFO_BYTES],
			self::INFO_COUNT => $info[self::INFO_COUNT],
			self::INFO_LAST_RECEIVE_PID => $info[self::INFO_LAST_RECEIVE_PID],
			self::INFO_LAST_SEND_PID => $info[self::INFO_LAST_SEND_PID],
		];

		[
			'user' => $extends[self::INFO_UID],
			'group' => $extends[self::INFO_GID],
		] = Linux::userGroupToText($info[self::INFO_UID], $info[self::INFO_GID]);

		return $extends;
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
			;
		}
	}


	public function consumer(): Consumer
	{
		return $this->memoize(__METHOD__, function (): Consumer {
			return new Consumer($this);
		});
	}


	public function producer(): Producer
	{
		return $this->memoize(__METHOD__, function (): Producer {
			return new Producer($this);
		});
	}


	/**
	 * @return resource
	 */
	private function createResource()
	{
		return socket_create(AF_UNIX, SOCK_DGRAM, 0);
	}


	private static function createDateTime(int $timestamp): ?\DateTime
	{
		if ($timestamp === 0) {
			return null;
		}

		return (new \DateTime("@$timestamp"))->setTimezone(new \DateTimeZone(date_default_timezone_get()));
	}

}
