<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use h4kuna\Queue\Exceptions\CreateQueueException;
use h4kuna\Queue\SystemV\Msg;
use h4kuna\Queue\SystemV\MsgInterface;
use Nette\Utils\Finder;
use Nette\Utils\Random;

class QueueFactory
{

	public function __construct(
		protected /*readonly*/ int $permission = 0666,
		protected /*readonly*/ ?Dir $tempDir = null,
		protected /*readonly*/ int $messageSize = MsgInterface::MAX_MESSAGE_SIZE
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
		?Backup $backUp = null,
	): Queue
	{
		assert($this->tempDir !== null);
		$queueDir = $this->tempDir->dir('queue');

		$msg = $this->createMsg($queueDir, $name, $projectId, $permission, $messageSize);

		if ($backUp === null) {
			$backUp = $this->createBackup($queueDir->dir("message/$name"));
		}

		return new Queue($backUp, $msg);
	}


	protected function createMsg(
		Dir $queueDir,
		string $name,
		?string $projectId,
		?int $permission,
		?int $messageSize
	): MsgInterface
	{
		$filename = $queueDir->filename($name);
		is_file($filename) || touch($filename);

		if ($projectId === null) {
			if (preg_match('/(?<projectId>[a-z\d]{1})/i', $name, $match) === false) {
				throw new CreateQueueException(sprintf('Can not use project id from name "%s". Please let fill in factory constructor.', $name));
			}
			$projectId = $match['projectId'];
		}

		return new Msg($filename, $projectId, $permission ?? $this->permission, $messageSize ?? $this->messageSize);
	}


	protected function createBackup(Dir $messageDir): Backup
	{
		if (class_exists(Finder::class) && class_exists(Random::class)) {
			return new Backup\Filesystem($messageDir);
		}

		return new Backup\DevNull();
	}
}
