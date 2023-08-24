<?php declare(strict_types=1);

namespace h4kuna\Queue\Build;

use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\SystemV\MsgInterface;

interface Backup
{
	public function save(InternalMessage $internalMessage): void;


	public function needRestore(): bool;


	/**
	 * @return array<string>
	 */
	public function restore(MsgInterface $msg): array;


	public function remove(InternalMessage $internalMessage): void;
}
