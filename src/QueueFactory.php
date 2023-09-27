<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use h4kuna\Queue\Msg\Consumer;
use h4kuna\Queue\Msg\Producer;
use h4kuna\Queue\SystemV\Backup;
use h4kuna\Queue\SystemV\Backup\Filesystem;
use h4kuna\Queue\SystemV\Msg;
use h4kuna\Queue\SysvMsg\FtokFactory;

class QueueFactory
{
	protected Dir $tempDir;


	public function __construct(
		protected /*readonly*/ int $permission = 0666, // 0o666 from 8.1
		?Dir $tempDir = null,
		protected /*readonly*/ int $messageSize = MessageQueue::MAX_MESSAGE_SIZE
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
		$backUp = $this->createBackup($queueDir);

		$ftok = FtokFactory::create($queueDir, $name);

		return new Msg($name, $ftok, $this->permission, $backUp, $this->messageSize);
	}


	protected function createBackup(Dir $messageDir): Backup
	{
		return new Filesystem($messageDir);
	}

}
