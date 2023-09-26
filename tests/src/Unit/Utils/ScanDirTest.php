<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit\Utils;

use h4kuna\Dir\TempDir;
use h4kuna\Queue\Tests\TestCase;
use h4kuna\Queue\Utils\ScanDir;
use Nette\Utils\Random;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ScanDirTest extends TestCase
{

	public function testContent(): void
	{
		$dir = new TempDir(Random::generate(4));

		Assert::same([], ScanDir::content($dir));

		touch($dir->filename('b'));
		Assert::same(['b'], ScanDir::content($dir));

		touch($dir->filename('a'));
		Assert::same(['a', 'b'], ScanDir::content($dir));
	}

}

(new ScanDirTest())->run();
