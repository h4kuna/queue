<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\Exceptions;

final class Producer
{
	private const BLOCKING = true;
	private const NO_BLOCKING = false;

	/** @var Queue */
	private $queue;


	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
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

		$error = 0;
		$success = @msg_send($this->queue->resource(), $messageType, $message, Config::NO_SERIALIZE, $blocking, $error);
		if (!$success || $error !== 0) {
			switch ($error) {
				case 11:
					try {
						$bytesSize = $this->queue->info()[$this->queue::INFO_BYTES];
					} catch (Exceptions\QueueInfoIsUnavailableException $e) {
						$bytesSize = 'unavailable';
					}

					throw new Exceptions\SendException(sprintf('Queue "%s" is full, allowed size is "%s".', $this->queue->fullname(), $bytesSize));
				case 22:
					throw new Exceptions\SendException(sprintf('Message is too long for queue "%s", allowed size is "%s" and you have "%s".', $this->queue->fullname(), $this->queue->messageSizeBytes(), strlen($message)));
			}
			throw new Exceptions\SendException(sprintf('Message is not saved to queue "%s" with code "%s".', $this->queue->fullname(), $error), $error);
		}
	}

}
