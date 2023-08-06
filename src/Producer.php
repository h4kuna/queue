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


	public function send(string|InternalMessage $message, int $messageType = Config::TYPE_DEFAULT): void
	{
		$this->save($message, $messageType, self::BLOCKING);
	}


	public function sendNonBlocking(string|InternalMessage $message, int $messageType = Config::TYPE_DEFAULT): void
	{
		$this->save($message, $messageType, self::NO_BLOCKING);
	}


	private function save(string|InternalMessage $message, int $messageType, bool $blocking): void
	{
		if ($messageType <= 0) {
			throw new Exceptions\SendException('Message type MUST be greater than 0.');
		}

		if (is_string($message)) {
			$internalMessage = $this->backup->save($message, $messageType, $blocking);
		} else {
			$internalMessage = $message;
		}

		$this->msg->send($internalMessage);
	}

}
