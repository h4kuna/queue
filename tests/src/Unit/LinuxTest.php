<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Unit;

use h4kuna\Queue\Linux;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class LinuxTest extends TestCase
{
	/**
	 * @return array<array<mixed>>
	 */
	protected function provideDataPermissionInToText(): array
	{
		return [
			[0777, 'rwxrwxrwx'],
			[0000, '---------'],
			[0001, '--------x'],
			[0002, '-------w-'],
			[0003, '-------wx'],
			[0004, '------r--'],
			[0005, '------r-x'],
			[0006, '------rw-'],
			[0007, '------rwx'],
			[0010, '-----x---'],
			[0020, '----w----'],
			[0030, '----wx---'],
			[0040, '---r-----'],
			[0050, '---r-x---'],
			[0060, '---rw----'],
			[0070, '---rwx---'],
			[0100, '--x------'],
			[0200, '-w-------'],
			[0300, '-wx------'],
			[0400, 'r--------'],
			[0500, 'r-x------'],
			[0600, 'rw-------'],
			[0700, 'rwx------'],
		];
	}


	/**
	 * @dataProvider provideDataPermissionInToText
	 */
	public function testPermissionInToText(int $input, string $expected): void
	{
		Assert::same($expected, Linux::permissionInToText($input));
	}
}

(new LinuxTest())->run();
