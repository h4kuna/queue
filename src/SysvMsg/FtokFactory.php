<?php declare(strict_types=1);

namespace h4kuna\Queue\SysvMsg;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Exceptions\CreateQueueException;

final class FtokFactory
{

	public static function create(Dir $dir, string $name): int
	{
		if (preg_match('/(?<projectId>[a-z\d]{1})/i', $name, $match) === false) {
			throw new CreateQueueException(sprintf('Can not use project id from name "%s". Please let fill in factory constructor.', $name));
		}

		$projectId = $match['projectId'];
		$file = $dir->filename('../.' . $name);
		if (touch($file) === false) {
			throw new CreateQueueException(sprintf('Queue "%s" failed to create. Probably file does not exists "%s".', $name, $dir->getDir()));
		}

		$key = ftok($file, $projectId);
		if ($key === -1) {
			throw new CreateQueueException(sprintf('Queue "%s" failed to create. Probably file does not exists "%s" or project id "%s" is not valid.',
				$name, $dir->getDir(), $projectId));
		}

		return $key;
	}

}
