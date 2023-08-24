<?php declare(strict_types=1);

namespace h4kuna\Queue\Msg;

use h4kuna\Queue\Build\Backup;
use h4kuna\Queue\Config;
use h4kuna\Queue\Exceptions;
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


	public function sendNonBlocking(string $message, int $messageType = Config::TYPE_DEFAULT): bool
	{
		try {
			$this->save($message, $messageType, self::NO_BLOCKING);
		} catch (Exceptions\SendException $e) {
			if ($e->getCode() === Config::QUEUE_IS_FULL) {
				return false;
			}
			throw $e;
		}
		return true;
	}


	private function save(string $message, int $messageType, bool $blocking): void
	{
		$internalMessage = new InternalMessage($message, $messageType, $blocking);
		$this->msg->send($internalMessage);
		$this->backup->save($internalMessage);
	}

}
