<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit\Backup;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Backup\Filesystem;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\Tests\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class FilesystemTest extends TestCase
{

	public function testBasic(): void
	{
		$filesystem = new Filesystem((new Dir(__DIR__ . '/../../../temp'))->dir('filesystemTest'));
		$internalMessage = $filesystem->save('foo', 3, true);
		Assert::type(InternalMessage::class, $internalMessage);
		Assert::same('foo', $internalMessage->message);
		Assert::same(3, $internalMessage->type);
		Assert::true($internalMessage->isBlocking);
		Assert::same(10, strlen($internalMessage->id));
		Assert::notSame('', $internalMessage->serialized());

		Assert::true($filesystem->needRestore());
		$filesystem->remove($internalMessage);
		Assert::false($filesystem->needRestore());
	}

}

(new FilesystemTest())->run();
