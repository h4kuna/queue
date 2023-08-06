<?php declare(strict_types=1);

namespace h4kuna\Queue;

use h4kuna\Queue\Exceptions\ReceiveException;
use h4kuna\Queue\SystemV\MsgInterface;

final class Consumer
{

	public function __construct(private Backup $backup, private MsgInterface $msg)
	{
	}


	private function read(int $messageType, int $flags): Message
	{
		$internalMessage = $this->msg->receive($messageType, $flags);
		$this->backup->remove($internalMessage);

		return $internalMessage->createMessage();
	}


	public function receive(int $messageType = Config::TYPE_DEFAULT): Message
	{
		return $this->read($messageType, 0);
	}


	public function tryReceive(int $messageType = Config::TYPE_DEFAULT): ?Message
	{
		try {
			return $this->read($messageType, MSG_IPC_NOWAIT);
		} catch (ReceiveException $e) {
			if ($e->getCode() === MSG_ENOMSG) {
				return null;
			}
			throw $e;
		}
	}

}
