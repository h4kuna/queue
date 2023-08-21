<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Queue\Backup;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\SystemV\MsgInterface;

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


	public function restore(MsgInterface $msg): array
	{
		return [];
	}


	public function remove(InternalMessage $internalMessage): void
	{
	}

}
