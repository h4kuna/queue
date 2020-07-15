<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\Exceptions\ReceiveException;

final class Consumer
{
	/** @var Queue */
	private $queue;


	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}


	public function tryReceive(int $messageType = Config::TYPE_DEFAULT): ?Message
	{
		try {
			return $this->read($messageType, MSG_IPC_NOWAIT);
		} catch (ReceiveException $e) {
			if ($e->getCode() === 42) {
				return NULL;
			}
			throw $e;
		}
	}


	public function receive(int $messageType = Config::TYPE_DEFAULT): Message
	{
		return $this->read($messageType, 0);
	}


	private function read(int $messageType, int $flags): Message
	{
		$message = '';
		$msgType = $error = 0;
		$success = msg_receive($this->queue->resource(), $messageType, $msgType, $this->queue->messageSizeBytes(), $message, Config::NO_SERIALIZE, $flags, $error);

		if (!$success || $error !== 0) {
			throw new ReceiveException(sprintf('Message received failed "%s", with code "%s" and error message "%s".', $this->queue->fullname(), $error, self::errorMessage()), $error);
		}

		// WARNING if $messageType is negative, than all $msgType are set 1
		return new Message($message, $msgType);
	}


	private static function errorMessage(): string
	{
		$message = error_get_last();
		return $message['message'] ?? '';
	}

}
