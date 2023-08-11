<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Dir\Dir;
use h4kuna\Queue;
use h4kuna\Queue\Tests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../TestCase.php';

/**
 * @testCase
 */
final class QueueFactoryTest extends TestCase
{

	public function testReceive(): void
	{
		$queueFactory = new Queue\QueueFactory(tempDir: new Dir(__DIR__ . '/../../temp'));

		$queue = $queueFactory->create('my-queue');

		$queue->producer()->send('Hello, "how are you"?');

		Assert::same('Hello, "how are you"?', $queue->consumer()->receive()->message);
	}


	public function testTryReceive(): void
	{
		$queueFactory = new Queue\QueueFactory(tempDir: new Dir(__DIR__ . '/../../temp'));

		$queue = $queueFactory->create('my-queue-receive');

		Assert::null($queue->consumer()->tryReceive(0));
		Assert::null($queue->consumer()->tryReceive(0));
		Assert::null($queue->consumer()->tryReceive(0));

		$queue->producer()->send('Hello');

		Assert::same('Hello', $queue->consumer()->tryReceive()?->message);
	}

}

(new QueueFactoryTest)->run();
