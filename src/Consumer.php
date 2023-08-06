<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\Exceptions\ReceiveException;
use h4kuna\Serialize\Serialize;

final class Consumer
{

	public function __construct(private Queue $queue, private Backup $backup)
	{
	}


	private function read(int $messageType, int $flags): Message
	{
		$message = '';
		$msgType = $error = 0;
		$success = msg_receive(
			$this->queue->resource(),
			$messageType,
			$msgType,
			$this->queue->messageSizeBytes(),
			$message,
			Config::NO_SERIALIZE,
			$flags,
			$error
		);

		if ($error === 4 && function_exists('pcntl_signal_dispatch')) {
			pcntl_signal_dispatch();
		}

		if ($success === false || $error !== 0) {
			if ($error === 43) {
				throw new ReceiveException(sprintf('Another process remove queue "%s", error code "%s".',
					$this->queue->name(), $error), $error);
			}

			throw new ReceiveException(sprintf('Message received failed "%s", with code "%s".',
				$this->queue->name(), $error), $error);
		}

		$internalMessage = Serialize::decode($message);

		assert($internalMessage instanceof InternalMessage);
		$this->backup->remove($internalMessage);

		return new Message($internalMessage->message, $msgType);
	}


	public function receive(int $messageType = Config::TYPE_DEFAULT): Message
	{
		return $this->read($messageType, 0);
	}


	public function tryReceive(int $messageType = Config::TYPE_DEFAULT): ?Message
	{
		try {
			return $this->read($messageType, MSG_IPC_NOWAIT);
		} catch (ReceiveException $e) {
			if ($e->getCode() === MSG_ENOMSG) {
				return null;
			}
			throw $e;
		}
	}

}
