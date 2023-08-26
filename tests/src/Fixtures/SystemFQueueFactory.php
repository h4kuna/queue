<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Dir\Dir;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\QueueFactory;
use h4kuna\Queue\SystemF\MsgFactory;

final class SystemFQueueFactory extends QueueFactory
{
	protected function createMsg(
		Dir $queueDir,
		string $name,
	): MessageQueue
	{
		return (new MsgFactory())->create($this->permission, $queueDir);
	}

}
