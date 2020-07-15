<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class Message
{
	/** @var string */
	public $message;

	/** @var int */
	public $type;


	public function __construct(string $message, int $type)
	{
		$this->message = $message;
		$this->type = $type;
	}

}
