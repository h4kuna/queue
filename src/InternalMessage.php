<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Serialize\Serialize;

final class InternalMessage
{
	public string $id;

	private string $serialized = '';

	private int $receiveMsgType = 0;


	public function __construct(
		public string $message,
		public int $type,
		public bool $isBlocking,
	)
	{
		$this->id = self::random();
	}


	private static function random(): string
	{
		$right = bin2hex(random_bytes(2));
		$left = number_format(microtime(true), 4, '.', '');
		return "$left-$right";
	}


	public static function unserialize(string $content, int $receiveMessageType = 0): self
	{
		$internalMessage = Serialize::decode($content);
		assert($internalMessage instanceof self);
		$internalMessage->setReceiveMsgType($receiveMessageType);

		return $internalMessage;
	}


	public function serialized(): string
	{
		if ($this->serialized === '') {
			$this->serialized = Serialize::encode($this);
		}
		return $this->serialized;
	}


	public function __serialize(): array
	{
		return [
			'id' => $this->id,
			'message' => $this->message,
			'type' => $this->type,
			'isBlocking' => $this->isBlocking,
		];
	}


	/**
	 * @param array{id: string, message: string, type: int, isBlocking: bool} $data
	 */
	public function __unserialize(array $data): void
	{
		$this->id = $data['id'];
		$this->message = $data['message'];
		$this->type = $data['type'];
		$this->isBlocking = $data['isBlocking'];
	}


	public function createMessage(): Message
	{
		return new Message($this->message, $this->receiveMsgType);
	}


	private function setReceiveMsgType(int $msgType): void
	{
		$this->receiveMsgType = $msgType;
	}
}
