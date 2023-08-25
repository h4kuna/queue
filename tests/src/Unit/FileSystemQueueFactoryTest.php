<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Tests\Fixtures\SystemFQueueFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class FileSystemQueueFactoryTest extends TestCase
{

	public function testBasic(): void
	{
		$factory = new SystemFQueueFactory(tempDir: new Dir(__DIR__ . '/../../temp'));

		$queue = $factory->create('system-f');

		$queue->producer()->send('One');
		$queue->producer()->send('Two');
		$queue->producer()->send('Three', 2);
		$queue->producer()->send('Four', 2);

		$message = $queue->consumer()->receive();
		Assert::same('One', $message->message);

		$message = $queue->consumer()->receive();
		Assert::same('Two', $message->message);

		$message = $queue->consumer()->tryReceive();
		Assert::null($message);

		$message = $queue->consumer()->receive(2);
		Assert::same('Three', $message->message);

		$message = $queue->consumer()->receive(0);
		Assert::same('Four', $message->message);

		Assert::same('system-f', $queue->msg()->name());

		// $queue->consumer()->receive();
	}

}

(new FileSystemQueueFactoryTest())->run();
