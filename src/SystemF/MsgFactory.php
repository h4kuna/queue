<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

use h4kuna\Dir\Dir;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\SystemF\Lock\NoLock;

final class MsgFactory
{
	public function __construct(private float $sleep = 3.0)
	{
	}


	public function create(int $permission, Dir $dir, Dir $tempDir, ?Lock $lock = null): MessageQueue
	{
		$inotify = null;
		if (extension_loaded('inotify')) {
			$inotify = new Inotify($dir);
		}

		if ($lock === null) {
			$lock = new NoLock();
		}

		return new Msg($permission, $dir, $tempDir, new ActiveWait($this->sleep), $lock, $inotify);
	}

}
