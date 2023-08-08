<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit\Backup;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Backup\Filesystem;
use h4kuna\Queue\Consumer;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\Producer;
use h4kuna\Queue\Tests\Fixtures\BackupMock;
use h4kuna\Queue\Tests\Fixtures\MsgMock;
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
		Assert::notSame('', $internalMessage->serialized());

		Assert::true($filesystem->needRestore());
		$msg = new MsgMock();
		$backup = new BackupMock();
		$filesystem->restore(new Producer($backup, $msg));
		assert($msg->internalMessage !== null);

		Assert::same($internalMessage->message, $msg->internalMessage->message);
		Assert::same($internalMessage->id, $msg->internalMessage->id);
		Assert::same($internalMessage->type, $msg->internalMessage->type);
		Assert::same($internalMessage->isBlocking, $msg->internalMessage->isBlocking);
		Assert::notSame($internalMessage, $msg->internalMessage);

		$filesystem->remove($internalMessage);
		Assert::false($filesystem->needRestore());
	}

}

(new FilesystemTest())->run();
