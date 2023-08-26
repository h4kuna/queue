<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

use h4kuna\Dir\Dir;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\SysvMsg\FtokFactory;

final class MsgFactory
{
	public function __construct(private float $sleep = 3.0)
	{
	}


	public function create(int $permission, Dir $dir): MessageQueue
	{
		$inotify = null;
		if (extension_loaded('inotify')) {
			$inotify = new Inotify($dir);
		}

		$name = basename($dir->getDir());
		$mutex = new Mutex($name, FtokFactory::create($dir, $name));

		return new Msg($permission, $dir, new ActiveWait($this->sleep), $mutex, $inotify);
	}

}
