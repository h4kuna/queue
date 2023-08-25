<?php declare(strict_types=1);

namespace h4kuna\Queue\Utils;

use h4kuna\Dir\Dir;

final class ScanDir
{

	/**
	 * @return array<string>
	 */
	public static function content(Dir $dir): array
	{
		$files = scandir($dir->getDir(), SCANDIR_SORT_ASCENDING);
		if ($files === false) {
			return [];
		}
		array_shift($files);
		array_shift($files);

		return $files;
	}
}
