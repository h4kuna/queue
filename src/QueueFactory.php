<?php declare(strict_types=1);

namespace h4kuna\Queue;

use Nette\Utils\FileSystem;
use Nette\Utils\Validators;

class QueueFactory
{
	/** @var string */
	private $tempDir;

	/** @var int */
	private $permission;

	/** @var int */
	private $messageSize;


	public function __construct(
		string $tempDir = '',
		int $permission = 0600,
		int $messageSize = Queue::MAX_MESSAGE_SIZE
	)
	{
		$this->tempDir = $tempDir === '' ? sys_get_temp_dir() : $tempDir;
		$this->permission = $permission;
		$this->messageSize = $messageSize;
	}


	/**
	 * @param int|string $name - [name.id] is possible
	 */
	public function create($name, int $permission = null): Queue
	{
		if ($permission === null) {
			$permission = $this->permission;
		}

		FileSystem::createDir($this->tempDir);
		$socketFile = $this->tempDir . DIRECTORY_SEPARATOR . $name . '.sock';
		@unlink($socketFile);

		return new Queue($socketFile, $permission, $this->messageSize);
	}


	protected function getPermission(): int
	{
		return $this->permission;
	}


	protected function getMessageSize(): int
	{
		return $this->messageSize;
	}


	protected static function generateKey(string $name, int $permission, int $messageSize): int
	{
		return crc32("$name.$permission.$messageSize");
	}


	/**
	 * @return array{name: string, key: int}
	 */
	protected static function divideName(string $name): array
	{
		$explodeName = explode('.', $name, 2);
		if (isset($explodeName[1]) && Validators::isNumericInt($explodeName[1])) {
			return ['name' => $explodeName[0], 'key' => (int) $explodeName[1]];
		}
		return [];
	}

}
