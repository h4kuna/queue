<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\Tests\TestCase;
use h4kuna\Serialize\Driver\IgBinary;
use h4kuna\Serialize\Driver\Php;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../bootstrap.php';

final class CompareSerializeBehavior extends TestCase
{
	/**
	 * @return array<array<string>>
	 */
	protected function dataSerialize(): array
	{
		return [
			['Hello, "how are you"?'],
			['Hello, ""\\how are \, \\,you"?'],
			['Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In dapibus augue non sapien. Duis sapien nunc, commodo et, interdum suscipit, sollicitudin et, dolor. Fusce suscipit libero eget elit. Etiam ligula pede, sagittis quis, interdum ultricies, scelerisque eu. Nunc tincidunt ante vitae massa. Integer imperdiet lectus quis justo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Vivamus luctus egestas leo. Vivamus ac leo pretium faucibus. Morbi leo mi, nonummy eget tristique non, rhoncus non leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.'],
			['Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In dapibus augue non sapien. Duis sapien nunc, commodo et, interdum suscipit, sollicitudin et, dolor. Fusce suscipit libero eget elit.'],
		];
	}


	/**
	 * @dataProvider dataSerialize
	 */
	public function testSerialize(string $message): void
	{
		$internalMessage = new TestInternalMessage($message, 3, true);

		$igBinary = strlen(IgBinary::encode($internalMessage));
		$serialize = strlen(Php::encode($internalMessage));
		$csv = strlen($internalMessage->serialized());
		$igBinaryCsv = strlen(IgBinary::encode($internalMessage->serialized()));

		$compress = strlen(self::gzcompress($internalMessage->serialized()));

		Assert::true($igBinary < $serialize);
		Assert::true($csv < $igBinary);
		Assert::true($csv < $igBinaryCsv);

		dump(sprintf('csv: %s, igBinaryCsv: %s, igBinary: %s, serialize: %s, compress: %s, igbinaryCompress: %s', $csv, $igBinaryCsv, $igBinary, $serialize, $compress, strlen(self::gzcompress(IgBinary::encode($internalMessage)))));
	}


	private static function gzcompress(string $string): string
	{
		$data = gzcompress($string);
		if ($data === false) {
			throw new \RuntimeException('gzcompress failed.');
		}

		return $data;
	}

}

// \Tester\Environment::skip('Is only for information.');

class TestInternalMessage extends InternalMessage
{
	public function __serialize(): array
	{
		return [
			0 => $this->id,
			1 => $this->message,
			2 => $this->type,
			3 => $this->isBlocking,
		];
	}


	/**
	 * @param array{0: string, 1: string, 2: int, 3: bool} $data
	 */
	public function __unserialize(array $data): void
	{
		$this->id = $data[0];
		$this->message = $data[1];
		$this->type = $data[2];
		$this->isBlocking = $data[3];
	}
}

Environment::skip('Only information character.');

(new CompareSerializeBehavior())->run();
