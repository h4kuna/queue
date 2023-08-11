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
			0 => $this->id,
			1 => $this->message,
			2 => $this->type,
			3 => $this->isBlocking,
		];
	}


	/**
	 * @param array{0: string, 1: string, 2: int, 3: bool} $data
	 */
	public function __unserialize(array $data): void
	{
		$this->id = $data[0];
		$this->message = $data[1];
		$this->type = $data[2];
		$this->isBlocking = $data[3];
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
