<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class Producer
{
	private const BLOCKING = true;
	private const NO_BLOCKING = false;


	public function __construct(private Queue $queue, private Backup $backup)
	{
	}


	public function send(string|InternalMessage $message, int $messageType = Config::TYPE_DEFAULT): void
	{
		$this->save($message, $messageType, self::BLOCKING);
	}


	private function save(string|InternalMessage $message, int $messageType, bool $blocking): void
	{
		if ($messageType <= 0) {
			throw new Exceptions\SendException('Message type MUST be greater than 0.');
		}

		$error = 0;
		if (is_string($message)) {
			$internalMessage = $this->backup->save($message, $messageType, $blocking);
		} else {
			$internalMessage = $message;
			$blocking = $message->isBlocking;
			$messageType = $message->type;
		}

		$success = @msg_send($this->queue->resource(), $messageType, $internalMessage->serialized(), Config::NO_SERIALIZE, $blocking, $error);
		if (!$success || $error !== 0) {
			switch ($error) {
				case 11:
					try {
						$bytesSize = $this->queue->info()[$this->queue::INFO_SETUP_BYTES];
					} catch (Exceptions\QueueInfoIsUnavailableException $e) {
						$bytesSize = 'unavailable';
					}

					throw new Exceptions\SendException(sprintf('Queue "%s" is full, allowed size is "%s".', $this->queue->name(), $bytesSize));
				case 22:
					throw new Exceptions\SendException(sprintf('Message is too long for queue "%s", allowed size is "%s" and you have "%s".', $this->queue->name(), $this->queue->messageSizeBytes(), strlen($internalMessage->message)));
			}
			throw new Exceptions\SendException(sprintf('Message is not saved to queue "%s" with code "%s".', $this->queue->name(), $error), $error);
		}
	}


	public function sendNonBlocking(string|InternalMessage $message, int $messageType = Config::TYPE_DEFAULT): void
	{
		$this->save($message, $messageType, self::NO_BLOCKING);
	}

}
