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
			return $this->read($messageType, MSG_DONTWAIT);
		} catch (ReceiveException $e) {
			if ($e->getCode() === MSG_ENOMSG) {
				return null;
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
		$socket = $this->queue->resource();
		$file = $this->queue->filename();
		if (socket_bind($socket, $file) === false) {
			throw new ReceiveException('Could not use socket file.');
		}

		if (socket_recvfrom($socket, $message, $this->queue->messageSizeBytes(), $flags, $source) === false) {
			throw new ReceiveException('Any bug!');
		}

		return new Message($message, 0);
	}

}
