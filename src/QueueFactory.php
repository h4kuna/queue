<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class QueueFactory
{

	public function create(string $name, int $permission = 0666, int $maxMessageSize = Queue::MAX_MESSAGE_SIZE_NO_DEFINED): Queue
	{
		return new Queue($name, crc32("$name.$permission.$maxMessageSize"), $permission, $maxMessageSize);
	}

}
