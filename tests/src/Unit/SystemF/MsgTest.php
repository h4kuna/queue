<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit\SystemF;

use h4kuna\Dir\TempDir;
use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\SystemF\Msg;
use h4kuna\Queue\SystemF\MsgFactory;
use h4kuna\Queue\Tests\TestCase;
use Nette\Utils\Random;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class MsgTest extends TestCase
{

	public function testRead(): void
	{
		$msg = self::createMsg();

		$messages = [];
		foreach ($msg->read() as $message) {
			$messages[] = $message;
		}

		Assert::same([], $messages);
	}


	public function testSendReceive(): void
	{
		$msg = self::createMsg();

		Assert::same(null, $msg->receive(1, 1));

		$internalMessage = new InternalMessage('ok', 1, false);
		$msg->send($internalMessage);

		Assert::same('ok', $msg->receive(1, 0)->message);
	}


	private static function createMsg(): Msg
	{
		$dir = new TempDir(Random::generate(4));

		$msg = (new MsgFactory())->create(0666, $dir, $dir->dir('..'));
		assert($msg instanceof Msg);

		return $msg;
	}

}

(new MsgTest())->run();
