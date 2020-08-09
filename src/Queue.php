<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Memoize\MemoryStorage;
use h4kuna\Queue\Exceptions\CreateQueueException;

final class Queue
{
	use MemoryStorage;

	public const MAX_MESSAGE_SIZE = 8192;

	/** @var string */
	private $name;

	/** @var int */
	private $key;

	/** @var int */
	private $permission;

	/** @var resource|NULL */
	private $queueId;

	/** @var int */
	private $maxMessageSize;


	public function __construct(string $name, int $key, int $permission, int $maxMessageSize = self::MAX_MESSAGE_SIZE)
	{
		$this->name = $name;
		$this->key = $key;
		$this->permission = $permission;
		$this->maxMessageSize = $maxMessageSize;
	}


	public function name(): string
	{
		return $this->name;
	}


	public function fullname(): string
	{
		return $this->name . '.' . $this->key;
	}


	public function resource()
	{
		if ($this->queueId === null) {
			$this->queueId = $this->createResource();
		}
		return $this->queueId;
	}


	public function remove(): bool
	{
		$result = msg_remove_queue($this->resource());
		$this->queueId = null;
		return $result;
	}


	public function setup(array $data): bool
	{
		$structure = ['msg_perm.uid', 'msg_perm.gid', 'msg_perm.mode', 'msg_qbytes'];

		return msg_set_queue($this->resource(), array_intersect_key($data, array_fill_keys($structure, true)));
	}


	public function info(): array
	{
		return msg_stat_queue($this->resource());
	}


	public function messageSizeBytes(): int
	{
		return $this->maxMessageSize;
	}


	public function inQueue(): int
	{
		return $this->info()['msg_qnum'];
	}


	public function flush(): void
	{
		if ($this->inQueue() !== 0) {
			$consumer = $this->consumer();
			while ($consumer->tryReceive(Config::TYPE_ALL) !== null) {
				;
			}
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


	public function sizeBytes(): int
	{
		return $this->info()['msg_qbytes'];
	}


	/**
	 * @return resource
	 */
	private function createResource()
	{
		$exists = msg_queue_exists($this->key);
		$queue = msg_get_queue($this->key, $this->permission);

		if ($exists && self::queuePermission($queue) !== $this->permission) {
			throw new CreateQueueException(sprintf('Queue "%s" already exists with permissions "%s" and you require "%s".', $this->key, self::queuePermission($queue), $this->permission));
		}

		return $queue;
	}


	/**
	 * @param resource $queue
	 */
	private static function queuePermission($queue): int
	{
		$stats = msg_stat_queue($queue);
		if (!is_array($stats)) {
			// bad initialized queue
			@msg_remove_queue($queue); // intentionally @
			throw new CreateQueueException('Bad initialize message queue, let\'s repeat. Now is deleted.');
		}

		return $stats['msg_perm.mode'];
	}

}
