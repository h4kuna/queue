<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Queue\Config;
use h4kuna\Queue\Msg\InternalMessage;
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
			[new InternalMessage('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In dapibus augue non sapien. Duis sapien nunc, commodo et, interdum suscipit, sollicitudin et, dolor. Fusce suscipit libero eget elit.', 3, true)],
			[new InternalMessage('Hello, ""\\how are \, \\,you"?', 1, false)],
		];
	}


	/**
	 * @dataProvider dataSerialize
	 */
	public function testSerialize(InternalMessage $internalMessage): void
	{
		$internalMessage2 = InternalMessage::unserialize($internalMessage->serialized(), 1);

		Assert::same($internalMessage->serialized(), $internalMessage2->serialized());
		Assert::notSame($internalMessage, $internalMessage2);
	}


	public function testMinimalSize(): void
	{
		Assert::same(Config::MINIMAL_QUEUE_SIZE, strlen((new InternalMessage('', 1, true))->serialized()));
	}
}

(new InternalMessageTest())->run();
