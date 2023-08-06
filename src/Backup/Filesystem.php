<?php declare(strict_types=1);

namespace h4kuna\Queue\Backup;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Backup;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\Producer;
use h4kuna\Serialize\Serialize;
use Nette\Utils\Finder;
use Nette\Utils\Random;
use SplFileInfo;

final class Filesystem implements Backup
{
	public function __construct(private Dir $dir)
	{
	}


	public function save(string $message, int $messageType, bool $blocking): InternalMessage
	{
		$internalMessage = new InternalMessage(Random::generate(), $message, $messageType, $blocking);
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


	public function restore(Producer $producer): void
	{
		$files = Finder::findFiles('*')->in($this->dir->getDir());

		foreach ($files as $file) {
			$producer->send($this->restoreMessage($file));
		}
	}


	private function restoreMessage(SplFileInfo $file): InternalMessage
	{
		$content = file_get_contents($file->getPathname());
		assert(is_string($content));

		$internalMessage = Serialize::decode($content);
		assert($internalMessage instanceof InternalMessage);

		return $internalMessage;
	}
}
