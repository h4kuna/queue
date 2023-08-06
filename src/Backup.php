<?php declare(strict_types=1);

namespace h4kuna\Queue;

interface Backup
{
	public function save(string $message, int $messageType, bool $blocking): InternalMessage;


	public function needRestore(): bool;


	public function restore(Producer $producer): void;


	public function remove(InternalMessage $internalMessage): void;
}
