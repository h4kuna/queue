<?php declare(strict_types=1);

namespace h4kuna\Queue\Backup;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Backup;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\SystemV\MsgInterface;

final class Filesystem implements Backup
{
	public function __construct(private Dir $dir)
	{
	}


	public function save(InternalMessage $internalMessage): void
	{
		$path = $this->dir->filename($internalMessage->id);
		file_put_contents($path, $internalMessage->serialized());
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


	public function restore(MsgInterface $msg): array
	{
		$files = scandir($this->dir->getDir(), SCANDIR_SORT_ASCENDING);
		if ($files === false) {
			return [];
		}

		$messages = [];
		foreach ($files as $file) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$content = file_get_contents($this->dir->filename($file));
			assert(is_string($content));

			$internalMessage = InternalMessage::unserialize($content);
			$msg->send($internalMessage);
			$messages[] = $internalMessage->message;
		}

		return $messages;
	}

}
