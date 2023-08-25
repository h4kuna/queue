<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

final class ActiveWait
{

	public function __construct(
		private float $sleep = 0.1,
	)
	{
	}


	public function run(callable $callback): void
	{
		run:
		$return = ($callback)();
		if ($return === false) {
			usleep((int) ($this->sleep * 1_000_000.0));
			goto run;
		}
	}

}
