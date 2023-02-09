<?php declare(strict_types=1);

namespace h4kuna\Queue;

class QueueFactory
{

	public function __construct(private int $permission = 0666, private int $messageSize = Queue::MAX_MESSAGE_SIZE)
	{
	}


	/**
	 * @param int|string $name - [name.id] is possible
	 */
	public function create(int|string $name, int $permission = null): Queue
	{
		if ($permission === null) {
			$permission = $this->permission;
		}

		if (is_numeric($name)) {
			$key = (int) $name;
		} elseif (($explodeName = self::divideName($name)) !== null) {
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
	 * @return array{name: string, key: int}|null
	 */
	protected static function divideName(string $name): ?array
	{
		$explodeName = explode('.', $name, 2);
		if (isset($explodeName[1]) && self::isNumericInt($explodeName[1])) {
			return ['name' => $explodeName[0], 'key' => (int) $explodeName[1]];
		}

		return null;
	}


	private static function isNumericInt(int|string $name): bool
	{
		return is_numeric($name) && ((int) $name == (float) $name);
	}

}
