<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Dir\Dir;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\QueueFactory;
use h4kuna\Queue\SystemF\ActiveWait;
use h4kuna\Queue\SystemF\Msg;

final class SystemFQueueFactory extends QueueFactory
{
	protected function createMsg(
		Dir $queueDir,
		string $name,
		?string $projectId,
		?int $permission,
		?int $messageSize
	): MessageQueue
	{
		$activeWait = new ActiveWait(2.0);

		return new Msg($this->permission, $queueDir, $activeWait);
	}

}
