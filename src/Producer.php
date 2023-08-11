<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\SystemV\MsgInterface;

final class Producer
{
	private const BLOCKING = true;
	private const NO_BLOCKING = false;


	public function __construct(private Backup $backup, private MsgInterface $msg)
	{
	}


	public function send(string $message, int $messageType = Config::TYPE_DEFAULT): void
	{
		$this->save($message, $messageType, self::BLOCKING);
	}


	public function sendNonBlocking(string $message, int $messageType = Config::TYPE_DEFAULT): void
	{
		$this->save($message, $messageType, self::NO_BLOCKING);
	}


	private function save(string $message, int $messageType, bool $blocking): void
	{
		if ($messageType <= 0) {
			throw new Exceptions\SendException('Message type MUST be greater than 0.');
		}

		$this->msg->send(
			$this->backup->save($message, $messageType, $blocking)
		);
	}

}
