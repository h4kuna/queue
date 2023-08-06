<?php declare(strict_types=1);

namespace h4kuna\Queue\Backup;

use h4kuna\Queue\Backup;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\Producer;

final class DevNull implements Backup
{
	public function save(string $message, int $messageType, bool $blocking): InternalMessage
	{
		return new InternalMessage('', $message, $messageType, $blocking);
	}


	public function needRestore(): bool
	{
		return false;
	}


	public function restore(Producer $producer): void
	{
	}


	public function remove(InternalMessage $internalMessage): void
	{
	}

}
