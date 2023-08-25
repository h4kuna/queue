<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\MessageQueue;

final class MsgMock implements MessageQueue
{
	public ?InternalMessage $internalMessage = null;


	public function send(InternalMessage $internalMessage): void
	{
		$this->internalMessage = $internalMessage;
	}


	public function receive(int $messageType, int $flags): ?InternalMessage
	{
		return $this->internalMessage;
	}


	public function setup(array $data): bool
	{
		return true;
	}


	public function info(): array
	{
		return [];
	}


	public function name(): string
	{
		return 'test';
	}


	public function remove(): bool
	{
		return true;
	}

}
