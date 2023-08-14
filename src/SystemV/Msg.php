<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemV;

use h4kuna\Queue\Config;
use h4kuna\Queue\Exceptions;
use h4kuna\Queue\InternalMessage;
use SysvMessageQueue;

final class Msg implements MsgInterface
{
	private ?SysvMessageQueue $resource = null;


	public function __construct(
		private string $filename,
		private string $projectId,
		private int $permission,
		private int $maxMessageSize = self::MAX_MESSAGE_SIZE,
	)
	{
		if ($this->maxMessageSize <= Config::MINIMAL_QUEUE_SIZE) {
			throw new Exceptions\CreateQueueException(sprintf('Minimal queue size must be "%s" and you want "%s".', Config::MINIMAL_QUEUE_SIZE + 1, $this->maxMessageSize));
		}
	}


	public function send(InternalMessage $internalMessage): void
	{
		if (strlen($internalMessage->serialized()) > $this->maxMessageSize) {
			throw new Exceptions\SendException(sprintf('Message is too long for queue "%s", allowed size is "%s" and you have "%s".', $this->name(), $this->maxMessageSize, strlen($internalMessage->serialized())));
		}
		$error = 0;
		$success = @msg_send($this->resource(), $internalMessage->type, $internalMessage->serialized(), Config::NO_SERIALIZE, $internalMessage->isBlocking, $error);

		self::checkSuccessAndError($success, $error);

		switch ($error) {
			case 0:
				return; // ok
			case Config::QUEUE_IS_FULL:
				try {
					$bytesSize = $this->info()[self::INFO_SETUP_BYTES];
				} catch (Exceptions\QueueInfoIsUnavailableException) {
					$bytesSize = 'unavailable';
				}

				throw new Exceptions\SendException(sprintf('Queue "%s" is full, allowed size is "%s".', $this->name(), $bytesSize), $error);
			case 22:
				throw new Exceptions\SendException(sprintf('Message is too long for queue "%s", allowed size is "%s" and you have "%s".', $this->name(), $this->maxMessageSize, strlen($internalMessage->serialized())), $error);
		}
		throw new Exceptions\SendException(sprintf('Message is not saved to queue "%s" with code "%s".', $this->name(), $error), $error);
	}


	public function receive(int $messageType, int $flags): ?InternalMessage
	{
		$msgType = $error = 0;
		$success = msg_receive(
			$this->resource(),
			$messageType,
			$msgType,
			$this->maxMessageSize,
			$message,
			Config::NO_SERIALIZE,
			$flags,
			$error
		);

		self::checkSuccessAndError($success, $error);

		if ($error === 4 && function_exists('pcntl_signal_dispatch')) {
			pcntl_signal_dispatch();
		}

		switch ($error) {
			case 0:
				return InternalMessage::unserialize($message, $msgType); // ok
			case Config::QUEUE_ERROR:
				throw new Exceptions\ReceiveException(sprintf('Another process remove queue "%s", error code "%s".',
					$this->name(), $error), $error);
			case MSG_ENOMSG:
				return null;
			default:
				throw new Exceptions\ReceiveException(sprintf('Message received failed "%s", with code "%s".',
					$this->name(), $error), $error);
		}
	}


	public function info(): array
	{
		$info = msg_stat_queue($this->resource());
		if ($info === false) {
			throw new Exceptions\QueueInfoIsUnavailableException;
		}

		return $info;
	}


	public function setup(array $data): bool
	{
		$structure = [
			MsgInterface::INFO_SETUP_UID,
			MsgInterface::INFO_SETUP_GID,
			MsgInterface::INFO_SETUP_MODE,
			MsgInterface::INFO_SETUP_BYTES,
		];

		return msg_set_queue($this->resource(), array_intersect_key($data, array_fill_keys($structure, true)));
	}


	public function remove(): bool
	{
		if ($this->exists() === false) {
			return false;
		}
		$result = msg_remove_queue($this->resource());
		$this->clearResource();

		return $result;
	}


	public function name(): string
	{
		return basename($this->filename);
	}


	private function exists(): bool
	{
		return msg_queue_exists($this->queueKey());
	}


	private function resource(): SysvMessageQueue
	{
		if ($this->resource === null) {
			$this->resource = $this->createResource();
		};

		return $this->resource;
	}


	private function createResource(): SysvMessageQueue
	{
		$queue = msg_get_queue($this->queueKey(), $this->permission);

		if ($queue === false) {
			throw new Exceptions\CreateQueueException(sprintf('Queue "%s" failed to create.', $this->name()));
		}

		if ($this->exists() && ($perm = $this->queuePermission($queue)) !== $this->permission) {
			throw new Exceptions\CreateQueueException(sprintf('Queue "%s" already exists with permissions "%s" and you require "%s". %s',
				$this->name(), $perm, $this->permission, $this->helpHowRemove($perm)));
		}

		return $queue;
	}


	private function queuePermission(SysvMessageQueue $queue): int
	{
		$stats = msg_stat_queue($queue);
		if (!is_array($stats)) {
			throw new Exceptions\CreateQueueException(sprintf('Bad initialize message queue. %s',
				$this->helpHowRemove($this->permission)));
		}

		return $stats[self::INFO_SETUP_MODE];
	}


	private function queueKey(): int
	{
		$key = ftok($this->filename, $this->projectId);
		if ($key === -1) {
			throw new Exceptions\CreateQueueException(sprintf('Queue "%s" failed to create. Probably file does not exists "%s" or project id "%s" is not valid.',
				$this->name(), $this->filename, $this->projectId));
		}

		return $key;
	}


	private static function checkSuccessAndError(bool $success, int $error): void
	{
		if ($success === false && $error === 0) {
			throw new Exceptions\SendException('Message send failed. But error code is not set.');
		}
	}


	private function helpHowRemove(int $permission): string
	{
		return sprintf("Remove exist queue by cli: php -r 'msg_remove_queue(msg_get_queue(%s, %s));'", $this->queueKey(), $permission);
	}


	private function clearResource(): void
	{
		$this->resource = null;
	}
}
