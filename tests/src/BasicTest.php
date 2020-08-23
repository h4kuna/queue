<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests;

use h4kuna\Queue;
use Tester\Assert;

require_once __DIR__ . '/../TestCase.php';

final class BasicTest extends TestCase
{

	public function testReceive(): void
	{
		/** @var Queue\QueueFactory $queue */
		$queueFactory = new Queue\QueueFactory;

		/** @var Queue\Queue $queue */
		$queue = $queueFactory->create('my-queue');

		$queue->producer()->send('Hello');

		Assert::same('Hello', $queue->consumer()->receive()->message);
	}


	public function testTryReceive(): void
	{
		/** @var Queue\QueueFactory $queue */
		$queueFactory = new Queue\QueueFactory;

		/** @var Queue\Queue $queue */
		$queue = $queueFactory->create('my-queue');

		Assert::null($queue->consumer()->tryReceive(0));
		Assert::null($queue->consumer()->tryReceive(0));
		Assert::null($queue->consumer()->tryReceive(0));

		$queue->producer()->send('Hello');

		Assert::same('Hello', $queue->consumer()->tryReceive()->message);
	}

}

(new BasicTest)->run();
