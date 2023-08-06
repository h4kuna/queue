<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Serialize\Serialize;

final class InternalMessage
{
	private string $serialized = '';


	public function __construct(
		public string $id,
		public string $message,
		public int $type,
		public bool $isBlocking,
	)
	{
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
}
