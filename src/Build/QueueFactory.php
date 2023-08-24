<?php declare(strict_types=1);

namespace h4kuna\Queue\Build;

use h4kuna\Queue\Queue;

interface QueueFactory
{
	function create(
		string $name,
		?string $projectId = null,
		?int $permission = null,
		?int $messageSize = null,
		?Backup $backUp = null,
	): Queue;

}
