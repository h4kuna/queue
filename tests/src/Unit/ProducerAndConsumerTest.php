<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna;
use h4kuna\Queue\Msg\Consumer;
use h4kuna\Queue\Msg\Producer;
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
		$msg = new MsgMock();
		$producer = new Producer($msg);
		$consumer = new Consumer($msg);

		$producer->send('Hello');

		$message = $consumer->receive();
		Assert::same('Hello', $message->message);
	}


	/**
	 * @throws h4kuna\Queue\Exceptions\InvalidStateException
	 */
	public function testMessageTypeIsNegative(): void
	{
		$producer = new Producer(new MsgMock());
		$producer->send('Hello', -1);
	}

}

(new ProducerAndConsumerTest())->run();
