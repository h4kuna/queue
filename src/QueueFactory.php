<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use h4kuna\Queue\Msg\Consumer;
use h4kuna\Queue\Msg\Producer;
use h4kuna\Queue\SystemF\MsgFactory;
use h4kuna\Queue\SystemV\Backup\Filesystem;
use h4kuna\Queue\SysvMsg\FtokFactory;

class QueueFactory
{
	public const SYSTEM_V = 2;

	protected Dir $tempDir;


	public function __construct(
		protected /*readonly*/ int $permission = 0666, // 0o666 from 8.1
		?Dir $tempDir = null,
		protected /*readonly*/ int $messageSize = MessageQueue::MAX_MESSAGE_SIZE,
		private int $type = 1,
	)
	{
		$this->tempDir = $tempDir ?? new TempDir();
	}


	public function create(
		string $name,
		?int $messageSize = null,
	): Queue
	{
		$queueDir = $this->tempDir->dir($name);

		$oldMessageSize = $this->messageSize;
		$this->messageSize = $messageSize ?? $this->messageSize;
		$msg = $this->createMsg($queueDir, $name);

		$queue = new Queue(
			$msg,
			new Producer($msg),
			new Consumer($msg),
		);

		$this->messageSize = $oldMessageSize;

		return $queue;
	}


	protected function createMsg(
		Dir $queueDir,
		string $name,
	): MessageQueue
	{
		if ($this->type === self::SYSTEM_V) {
			return $this->createSystemV($queueDir, $name);
		}
		return $this->createSystemF($queueDir);
	}


	protected function createSystemF(Dir $queueDir): MessageQueue
	{
		return (new MsgFactory())->create($this->permission, $queueDir, $this->tempDir);
	}


	protected function createSystemV(Dir $queueDir, string $name): SystemV\Msg
	{
		$backUp = new Filesystem($queueDir);

		$ftok = FtokFactory::create($queueDir, $name);

		return new SystemV\Msg($name, $ftok, $this->permission, $backUp, $this->messageSize);
	}

}
