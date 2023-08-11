<?php declare(strict_types=1);

namespace h4kuna\Queue\Backup;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Backup;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\SystemV\MsgInterface;
use SplFileInfo;

final class Filesystem implements Backup
{
	public function __construct(private Dir $dir)
	{
	}


	public function save(string $message, int $messageType, bool $blocking): InternalMessage
	{
		$internalMessage = new InternalMessage($message, $messageType, $blocking);
		$path = $this->dir->filename($internalMessage->id);
		file_put_contents($path, $internalMessage->serialized());

		return $internalMessage;
	}


	public function needRestore(): bool
	{
		$dirs = scandir($this->dir->getDir());
		if ($dirs === false) {
			return false;
		}

		return count($dirs) > 2;
	}


	public function remove(InternalMessage $internalMessage): void
	{
		$path = $this->dir->filename($internalMessage->id);
		if (is_file($path)) {
			unlink($path);
		}
	}


	public function restore(MsgInterface $msg): void
	{
		$files = scandir($this->dir->getDir(), SCANDIR_SORT_ASCENDING);
		if ($files === false) {
			return;
		}

		foreach ($files as $file) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$content = file_get_contents($this->dir->filename($file));
			assert(is_string($content));

			$msg->send(InternalMessage::unserialize($content));
		}
	}

}
