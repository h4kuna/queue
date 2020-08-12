<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class QueueFactory
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


	public function create(string $name, int $permission = null): Queue
	{
		if ($permission === null) {
			$permission = $this->permission;
		}
		return new Queue($name, crc32("$name.$permission.{$this->messageSize}"), $permission, $this->messageSize);
	}

}
