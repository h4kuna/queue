<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use h4kuna\Queue\Backup\Filesystem;
use h4kuna\Queue\Exceptions\CreateQueueException;

class QueueFactory
{

	public function __construct(
		private int $permission = 0666,
		private ?Dir $tempDir = null,
		private int $messageSize = Queue::MAX_MESSAGE_SIZE
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
		int $permission = null,
		int $messageSize = null,
		?Backup $backUp = null,
	): Queue
	{
		if ($projectId === null) {
			if (preg_match('/(?<projectId>[a-z\d]{1})/i', $name, $match) === false) {
				throw new CreateQueueException(sprintf('Can not use project id from name "%s". Please let fill in factory constructor.', $name));
			}
			$projectId = $match['projectId'];
		}

		assert($this->tempDir !== null);
		$queueDir = $this->tempDir->dir('queue');
		$filename = $queueDir->filename($name);
		is_file($filename) || touch($filename);
		if ($backUp === null) {
			$backUp = new Filesystem($queueDir->dir("message/$name"));
		}

		$queue = new Queue($filename, $projectId, $backUp, $permission ?? $this->permission, $messageSize ?? $this->messageSize);

		if ($backUp->needRestore()) {
			$backUp->restore($queue->producer());
		}

		return $queue;
	}


	protected function getPermission(): int
	{
		return $this->permission;
	}


	protected function getMessageSize(): int
	{
		return $this->messageSize;
	}

}
