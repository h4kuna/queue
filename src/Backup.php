<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\SystemV\MsgInterface;

interface Backup
{
	public function save(string $message, int $messageType, bool $blocking): InternalMessage;


	public function needRestore(): bool;


	public function restore(MsgInterface $msg): void;


	public function remove(InternalMessage $internalMessage): void;
}
