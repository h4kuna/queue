<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Queue\InternalMessage;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class InternalMessageTest extends TestCase
{
	public function testSerialize(): void
	{
		$internalMessage = new InternalMessage('message', 3, true);
		$internalMessage2 = InternalMessage::unserialize($internalMessage->serialized(), 1);

		Assert::same($internalMessage->serialized(), $internalMessage2->serialized());
		Assert::notSame($internalMessage, $internalMessage2);
	}
}

(new InternalMessageTest())->run();
