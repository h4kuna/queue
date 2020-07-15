<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests;

use h4kuna\Queue;
use Tester\Assert;

require_once __DIR__ . '/../TestCase.php';

final class BasicTest extends TestCase
{

	public function testRun(): void
	{
		/** @var Queue\QueueFactory $queue */
		$queueFactory = new Queue\QueueFactory();

		/** @var Queue\Queue $queue */
		$queue = $queueFactory->create('my-queue');

		$queue->producer()->send('Hello');

		Assert::same('Hello', $queue->consumer()->receive()->message);
	}

}

(new BasicTest)->run();
