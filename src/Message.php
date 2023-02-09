<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class Message
{

	public function __construct(public string $message, public int $type)
	{
	}

}
