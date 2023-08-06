<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Queue\Backup;
use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\Producer;

final class BackupMock implements Backup
{
	public function __construct(private string $id = '1', private bool $needRestore = false)
	{
	}


	public function save(string $message, int $messageType, bool $blocking): InternalMessage
	{
		return new InternalMessage($this->id, $message, $messageType, $blocking);
	}


	public function needRestore(): bool
	{
		return $this->needRestore;
	}


	public function restore(Producer $producer): void
	{
	}


	public function remove(InternalMessage $internalMessage): void
	{
	}

}
