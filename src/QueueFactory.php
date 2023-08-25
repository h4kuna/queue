<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use h4kuna\Queue\Exceptions\CreateQueueException;
use h4kuna\Queue\Msg\Consumer;
use h4kuna\Queue\Msg\Producer;
use h4kuna\Queue\SystemV\Backup;
use h4kuna\Queue\SystemV\Backup\Filesystem;
use h4kuna\Queue\SystemV\Msg;

class QueueFactory
{

	public function __construct(
		protected /*readonly*/ int $permission = 0666, // 0o666 from 8.1
		protected /*readonly*/ ?Dir $tempDir = null,
		protected /*readonly*/ int $messageSize = MessageQueue::MAX_MESSAGE_SIZE
	)
	{
		$this->tempDir ??= new TempDir();
	}


	/**
	 * @param string|null $projectId only one char
	 */
	public function create(
		string $name,
		?string $projectId = null,
		?int $permission = null,
		?int $messageSize = null,
	): Queue
	{
		assert($this->tempDir !== null);
		$queueDir = $this->tempDir->dir($name);

		$msg = $this->createMsg($queueDir, $name, $projectId, $permission ?? $this->permission, $messageSize ?? $this->messageSize);

		return new Queue(
			$msg,
			new Producer($msg),
			new Consumer($msg),
		);
	}


	protected function createMsg(
		Dir $queueDir,
		string $name,
		?string $projectId,
		int $permission,
		int $messageSize
	): MessageQueue
	{
		$backUp = $this->createBackup($queueDir);

		$filename = $queueDir->getDir();

		if ($projectId === null) {
			if (preg_match('/(?<projectId>[a-z\d]{1})/i', $name, $match) === false) {
				throw new CreateQueueException(sprintf('Can not use project id from name "%s". Please let fill in factory constructor.', $name));
			}
			$projectId = $match['projectId'];
		}

		return new Msg($filename, $projectId, $permission, $backUp, $messageSize);
	}


	protected function createBackup(Dir $messageDir): Backup
	{
		return new Filesystem($messageDir);
	}

}
