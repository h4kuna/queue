<?php declare(strict_types=1);

namespace h4kuna\Queue;

use SplFileObject;

final class Linux
{
	/**
	 * @see https://www.php.net/manual/en/function.fileperms
	 */
	public static function permissionInToText(int $perms): string
	{
		$info = '';
		// Owner
		$info .= (($perms & 0x0100) === 0 ? '-' : 'r');
		$info .= (($perms & 0x0080) === 0 ? '-' : 'w');
		$info .= (($perms & 0x0040) === 0
			? (($perms & 0x0800) === 0 ? '-' : 'S')
			: (($perms & 0x0800) === 0 ? 'x' : 's'));

		// Group
		$info .= (($perms & 0x0020) === 0 ? '-' : 'r');
		$info .= (($perms & 0x0010) === 0 ? '-' : 'w');
		$info .= (($perms & 0x0008) === 0
			? (($perms & 0x0400) === 0 ? '-' : 'S')
			: (($perms & 0x0400) === 0 ? 'x' : 's'));

		// All
		$info .= (($perms & 0x0004) === 0 ? '-' : 'r');
		$info .= (($perms & 0x0002) === 0 ? '-' : 'w');
		$info .= (($perms & 0x0001) === 0
			? (($perms & 0x0200) === 0 ? '-' : 'T')
			: (($perms & 0x0200) === 0 ? 'x' : 't'));

		return $info;
	}


	/**
	 * @return array{user: string, group: string}
	 */
	public static function userGroupToText(int $user, int $group): array
	{
		$file = new SplFileObject('/etc/passwd');
		$file->setCsvControl(':', escape: '');
		$file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);

		$users = $groups = [];
		foreach ($file as $item) {
			/** @var array<string> $item */
			if (!isset($item[0])) {
				continue;
			}
			if (isset($item[2])) {
				$users[intval($item[2])] = $item[0];
			}
			if (isset($item[3])) {
				$groups[intval($item[3])] = $item[0];
			}
		}

		return ['user' => $users[$user] ?? '?', 'group' => $groups[$group] ?? '?'];
	}

}
