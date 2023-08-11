<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna;
use h4kuna\Queue\Consumer;
use h4kuna\Queue\Producer;
use h4kuna\Queue\Tests\Fixtures\BackupMock;
use h4kuna\Queue\Tests\Fixtures\MsgMock;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ProducerAndConsumerTest extends TestCase
{

	public function testBasic(): void
	{
		$backup = new BackupMock();
		$msg = new MsgMock();
		$producer = new Producer($backup, $msg);
		$consumer = new Consumer($backup, $msg);

		$producer->send('Hello');

		$message = $consumer->receive();
		Assert::same('Hello', $message->message);
	}


	/**
	 * @throws h4kuna\Queue\Exceptions\InvalidStateException
	 */
	public function testMessageTypeIsNegative(): void
	{
		$producer = new Producer(new BackupMock(), new MsgMock);
		$producer->send('Hello', -1);
	}

}

(new ProducerAndConsumerTest())->run();
