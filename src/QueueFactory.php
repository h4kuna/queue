<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class QueueFactory
{
	/** @var string */
	private $temp;


	public function __construct(?string $temp = NULL)
	{
		if ($temp === NULL) {
			$temp = sys_get_temp_dir();
		}
		$this->temp = $temp;
	}


	public function create(string $name, int $permission = 0666, int $maxMessageSize = Queue::MAX_MESSAGE_SIZE_NO_DEFINED): Queue
	{
		$file = $this->temp . DIRECTORY_SEPARATOR . $name;
		touch($file);
		$key = ftok($file, substr($name, 0, 1));

		return new Queue($name, $key, $permission, $maxMessageSize);
	}

}
