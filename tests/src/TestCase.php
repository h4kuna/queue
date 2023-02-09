<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests;

use Tester;

require_once __DIR__ . '/../bootstrap.php';

abstract class TestCase extends Tester\TestCase
{

	public function run(): void
	{
		if (\defined('__PHPSTAN_RUNNING__')) {
			return;
		}

		parent::run();
	}

}
