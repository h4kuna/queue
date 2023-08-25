<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemV\Backup;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\SystemV\Backup;
use h4kuna\Queue\Utils\ScanDir;

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
		return ScanDir::content($this->dir) !== [];
	}


	public function remove(InternalMessage $internalMessage): void
	{
		$path = $this->dir->filename($internalMessage->id);
		is_file($path) && unlink($path);
	}


	public function restore(MessageQueue $msg): array
	{
		$files = ScanDir::content($this->dir);

		$messages = [];
		foreach ($files as $file) {
			$content = file_get_contents($this->dir->filename($file));
			assert(is_string($content));

			$internalMessage = InternalMessage::unserialize($content);
			$msg->send($internalMessage);
			$messages[] = $internalMessage->message;
		}

		return $messages;
	}

}
