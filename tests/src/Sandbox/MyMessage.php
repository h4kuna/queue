<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Sandbox;

final class MyMessage
{
	public function __invoke(MyMessage $message): void
	{
		// Message processing...
	}
}
