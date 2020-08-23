<?php declare(strict_types=1);

namespace h4kuna\Queue;

use Nette\Utils\Validators;

class QueueFactory
{
	/** @var int */
	private $permission;

	/** @var int */
	private $messageSize;


	public function __construct(int $permission = 0666, int $messageSize = Queue::MAX_MESSAGE_SIZE)
	{
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

		if (Validators::isNumericInt($name)) {
			$key = (int) $name;
		} elseif (($explodeName = self::divideName($name)) !== []) {
			$name = $explodeName['name'];
			$key = $explodeName['key'];
		} else {
			$key = static::generateKey($name, $permission, $this->messageSize);
		}

		return new Queue((string) $name, $key, $permission, $this->messageSize);
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
