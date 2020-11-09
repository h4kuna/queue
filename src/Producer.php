<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\Exceptions;

final class Producer
{
	/** @var Queue */
	private $queue;


	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}


	public function send(string $message, int $messageType = Config::TYPE_DEFAULT): int
	{
		return $this->save($message, $messageType, 0);
	}


	public function sendNonBlocking(string $message, int $messageType = Config::TYPE_DEFAULT): int
	{
		return $this->save($message, $messageType, MSG_EOF);
	}


	private function save(string $message, int $messageType, int $flags): int
	{
		if ($messageType <= 0) {
			throw new Exceptions\SendException('Message type MUST be greater than 0.');
		}

		$length = strlen($message);
		if ($this->queue->messageSizeBytes() < $length) {
			throw new Exceptions\SendException('You want send big message, let\'s allow bigger size for message.');
		}

		$socket = $this->queue->resource();
		$file = $this->queue->filename();
		socket_bind($socket, $file);
		$result = socket_sendto($socket, $message, $length, $flags, $file, 0);
		socket_close($socket);

		if ($result === false) {
			throw new Exceptions\SendException(sprintf('Message is not saved to queue "%s" failed.', $this->queue->filename()));
		}

		return $result;
	}

}
