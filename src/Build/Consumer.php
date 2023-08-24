<?php declare(strict_types=1);

namespace h4kuna\Queue\Build;

use h4kuna\Memoize\MemoryStorage;
use h4kuna\Queue;

final class Consumer implements ConsumerAccessor
{
	use MemoryStorage;

	public function __construct(
		private Queue\Build\Backup $backup,
		private Queue\SystemV\MsgInterface $msg,
	)
	{
	}


	public function get(): Queue\Msg\Consumer
	{
		return $this->memoize(__METHOD__, fn (
		): Queue\Msg\Consumer => new Queue\Msg\Consumer($this->backup, $this->msg));
	}

}
