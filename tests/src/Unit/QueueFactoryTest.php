<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Dir\Dir;
use h4kuna\Queue;
use h4kuna\Queue\MessageQueue;
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
		$queueFactory = self::createQueueFactory();

		$queue = $queueFactory->create('my-queue');
		Assert::same('my-queue', $queue->msg()->name());

		$queue->producer()->send('Hello, "how are you"?');

		Assert::same('Hello, "how are you"?', $queue->consumer()->receive()->message);
	}


	public function testTryReceive(): void
	{
		$queueFactory = self::createQueueFactory();

		$size = Queue\Config::MINIMAL_QUEUE_SIZE + 4;
		$queue = $queueFactory->create('my-queue-receive', messageSize: $size);
		$queue->msg()->remove();
		Assert::true($queue->msg()->setup([MessageQueue::INFO_SETUP_BYTES => 128]));

		Assert::null($queue->consumer()->tryReceive(0));
		Assert::null($queue->consumer()->tryReceive(0));
		Assert::null($queue->consumer()->tryReceive(0));

		Assert::exception(fn (
		) => $queue->producer()->send('Hello'), Queue\Exceptions\SendException::class, sprintf('Message is too long for queue "my-queue-receive", allowed size is "%d" and you have "%d".', $size, $size + 1));

		Assert::null($queue->consumer()->tryReceive());

		for ($i = 0; $i < 4; ++$i) {
			Assert::true($queue->producer()->sendNonBlocking((string) $i));
		}
		Assert::false($queue->producer()->sendNonBlocking('5'));

		Assert::same(4, $queue->count());
		$queue->consumer()->flush();
		Assert::same(0, $queue->count());
	}


	private static function createQueueFactory(): Queue\QueueFactory
	{
		return new Queue\QueueFactory(tempDir: new Dir(__DIR__ . '/../../temp'), type: Queue\QueueFactory::SYSTEM_V);
	}

}

(new QueueFactoryTest)->run();
