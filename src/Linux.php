<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class Linux
{
	/**
	 * @var array<int, string>
	 */
	private static $permission = [
		4 => 'r',
		2 => 'w',
		1 => 'x',
	];


	public static function permissionInToText(int $permission): string
	{
		$value = decoct($permission);
		$permissionArray = array_map('intval', str_split((string) $value));
		$output = '';
		foreach ($permissionArray as $p) {
			foreach (self::$permission as $num => $str) {
				if ($p < $num) {
					$output .= '-';
				} else {
					$output .= $str;
					$p -= $num;
				}
			}
		}

		return $output;
	}


	/**
	 * @return array{user: string, group: string}
	 */
	public static function userGroupToText(int $user, int $group): array
	{
		$file = new \SplFileObject('/etc/passwd');
		$file->setCsvControl(':');
		$file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD);

		$users = $groups = [];
		foreach ($file as $item) {
			/** @var array $item */
			if (!isset($item[0])) {
				continue;
			}
			if (isset($item[2])) {
				$users[$item[2]] = $item[0];
			}
			if (isset($item[3])) {
				$groups[$item[3]] = $item[0];
			}
		}

		return ['user' => $users[$user] ?? '?', 'group' => $groups[$group] ?? '?'];
	}

}
