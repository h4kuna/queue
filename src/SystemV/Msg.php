<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemV;

use h4kuna\Queue\Config;
use h4kuna\Queue\Exceptions;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\Msg\InternalMessage;
use SysvMessageQueue;

final class Msg implements MessageQueue
{
	private ?SysvMessageQueue $resource = null;


	public function __construct(
		private string $name,
		private int $ftok,
		private int $permission,
		private Backup $backup,
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
				$this->backup->save($internalMessage);
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

		switch ($error) {
			case 0:
				$internalMessage = InternalMessage::unserialize($message, $msgType); // ok
				$this->backup->remove($internalMessage);

				return $internalMessage;
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
			MessageQueue::INFO_SETUP_UID,
			MessageQueue::INFO_SETUP_GID,
			MessageQueue::INFO_SETUP_MODE,
			MessageQueue::INFO_SETUP_BYTES,
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
		return $this->name;
	}


	/**
	 * @return array<string>
	 */
	public function restore(bool $remove = true): array
	{
		$remove && $this->remove();
		if ($this->backup->needRestore()) {
			$this->remove();
			return $this->backup->restore($this);
		}
		return [];
	}


	private function exists(): bool
	{
		return msg_queue_exists($this->ftok);
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
		$queue = msg_get_queue($this->ftok, $this->permission);

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


	private static function checkSuccessAndError(bool $success, int $error): void
	{
		if ($success === false && $error === 0) {
			throw new Exceptions\SendException('Message send failed. But error code is not set.');
		}
	}


	private function helpHowRemove(int $permission): string
	{
		return sprintf("Remove exist queue by cli: php -r 'msg_remove_queue(msg_get_queue(%s, %s));'", $this->ftok, $permission);
	}


	private function clearResource(): void
	{
		$this->resource = null;
	}
}
