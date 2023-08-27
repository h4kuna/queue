<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

interface Lock
{
	/**
	 * @template T
	 * @param callable():T $callback
	 * @return T
	 */
	function synchronized(callable $callback);
}
