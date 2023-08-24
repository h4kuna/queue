<?php declare(strict_types=1);

namespace h4kuna\Queue\Build;

use h4kuna\Memoize\MemoryStorage;
use h4kuna\Queue;

final class Producer implements ProducesAccessor
{
	use MemoryStorage;

	public function __construct(
		private Queue\Build\Backup $backup,
		private Queue\SystemV\MsgInterface $msg,
	)
	{
	}


	public function get(): Queue\Msg\Producer
	{
		return $this->memoize(__METHOD__, fn (
		): Queue\Msg\Producer => new Queue\Msg\Producer($this->backup, $this->msg));
	}

}
