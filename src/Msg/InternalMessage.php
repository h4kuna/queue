<?php declare(strict_types=1);

namespace h4kuna\Queue\Msg;

use h4kuna\Queue\Config;
use h4kuna\Queue\Exceptions\InvalidStateException;

class InternalMessage
{
	private string $serialized = '';

	private int $receiveMsgType = Config::TYPE_ALL;

	private bool $compress = false;


	public function __construct(
		public string $message,
		public int $type,
		public bool $isBlocking,
		public string $id = ''
	)
	{
		if ($this->type <= 0) {
			throw new InvalidStateException('Message type MUST be greater than 0.');
		}

		if ($this->id === '') {
			$this->id = self::random();
		}
	}


	private static function random(): string
	{
		$right = bin2hex(random_bytes(2));
		preg_match('/^0(?<dec>\.\d{6}).* (?<time>\d+)$/U', microtime(), $match);
		// microtime(true) has only 4 decimals I need 6
		assert(isset($match['time'], $match['dec']));

		return "{$match['time']}{$match['dec']}-$right";
	}


	public static function unserialize(string $content, int $receiveMessageType = Config::TYPE_ALL): self
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
		if (strlen($this->message) < 150 || extension_loaded('zlib') === false) {
			$message = $this->message;
		} else {
			$message = gzcompress($this->message);
			$this->compress = true;
		}

		return str_putcsv([
			$this->id,
			$message,
			$this->type,
			(int) $this->isBlocking,
			(int) $this->compress,
		]);
	}


	private static function unserializeCsv(string $content): self
	{
		$data = str_getcsv($content);
		assert(isset($data[0], $data[1], $data[2], $data[3], $data[4]));
		if (boolval($data[4])) {
			$data[1] = gzuncompress($data[1]);
			assert($data[1] !== false);
		}

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
