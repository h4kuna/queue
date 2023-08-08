<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Fixtures;

use h4kuna\Queue\InternalMessage;
use h4kuna\Queue\SystemV\MsgInterface;

final class MsgMock implements MsgInterface
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
