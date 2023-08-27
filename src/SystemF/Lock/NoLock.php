<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF\Lock;

use h4kuna\Queue\SystemF\Lock;

final class NoLock implements Lock
{
	public function synchronized(callable $callback)
	{
		return $callback();
	}

}
