<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
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
		int $messageSize = null
	): Queue
	{
		if ($projectId === null) {
			if (preg_match('/(?<projectId>[a-z\d]{1})/i', $name, $match) === false) {
				throw new CreateQueueException(sprintf('Can not use project id from name "%s". Please let fill in factory constructor.', $name));
			}
			$projectId = $match['projectId'];
		}

		assert($this->tempDir !== null);
		$filename = $this->tempDir->dir('queue')->filename($name);
		is_file($filename) || touch($filename);

		return new Queue($filename, $projectId, $permission ?? $this->permission, $messageSize ?? $this->messageSize);
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
