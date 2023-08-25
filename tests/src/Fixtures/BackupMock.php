<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\SystemV\Backup;

final class BackupMock implements Backup
{
	public function __construct(private bool $needRestore = false)
	{
	}


	public function save(InternalMessage $internalMessage): void
	{
	}


	public function needRestore(): bool
	{
		return $this->needRestore;
	}


	public function restore(MessageQueue $msg): array
	{
		return [];
	}


	public function remove(InternalMessage $internalMessage): void
	{
	}

}
