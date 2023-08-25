<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemV\Backup;

use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\SystemV\Backup;

final class DevNull implements Backup
{
	public function save(InternalMessage $internalMessage): void
	{
	}


	public function needRestore(): bool
	{
		return false;
	}


	public function restore(MessageQueue $msg): array
	{
		return [];
	}


	public function remove(InternalMessage $internalMessage): void
	{
	}

}
