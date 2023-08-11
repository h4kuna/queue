<?php declare(strict_types=1);

namespace h4kuna\Queue;

final class InternalMessage
{
	private string $serialized = '';

	private int $receiveMsgType = 0;


	public function __construct(
		public string $message,
		public int $type,
		public bool $isBlocking,
		public string $id = ''
	)
	{
		if ($this->id === '') {
			$this->id = self::random();
		}
	}


	private static function random(): string
	{
		$right = bin2hex(random_bytes(2));
		$left = number_format(microtime(true), 4, '.', '');
		return "$left-$right";
	}


	public static function unserialize(string $content, int $receiveMessageType = 0): self
	{
		$internalMessage = self::unserializeCsv($content);
		$internalMessage->setReceiveMsgType($receiveMessageType);

		return $internalMessage;
	}


	public function serialized(): string
	{
		if ($this->serialized === '') {
			$this->serialized = $this->serializeCsv();
		}
		return $this->serialized;
	}


	private function serializeCsv(): string
	{
		return str_putcsv([
			$this->id,
			$this->message,
			$this->type,
			$this->isBlocking,
		]);
	}


	private static function unserializeCsv(string $content): self
	{
		$data = str_getcsv($content);
		assert(isset($data[0], $data[1], $data[2], $data[3]));
		return new self($data[1], (int) $data[2], (bool) $data[3], $data[0]);
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
