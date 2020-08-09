<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class QueueFactory
{
	/** @var int */
	private $messageSize;


	public function __construct(int $messageSize = Queue::MAX_MESSAGE_SIZE)
	{
		$this->messageSize = $messageSize;
	}


	public function create(string $name, int $permission = 0666): Queue
	{
		return new Queue($name, crc32("$name.$permission.{$this->messageSize}"), $permission, $this->messageSize);
	}

}
