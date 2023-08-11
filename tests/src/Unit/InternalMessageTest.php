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

	/**
	 * @return array<array<InternalMessage>>
	 */
	protected function dataSerialize(): array
	{
		return [
			[new InternalMessage('Hello, "how are you"?', 3, true)],
			[new InternalMessage('Hello, ""\\how are \, \\,you"?', 0, false)],
		];
	}


	/**
	 * @dataProvider dataSerialize
	 */
	public function testSerialize($internalMessage): void
	{
		$internalMessage2 = InternalMessage::unserialize($internalMessage->serialized(), 1);

		Assert::same($internalMessage->serialized(), $internalMessage2->serialized());
		Assert::notSame($internalMessage, $internalMessage2);
	}
}

(new InternalMessageTest())->run();
