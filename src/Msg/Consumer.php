<?php declare(strict_types=1);

namespace h4kuna\Queue\Msg;

use h4kuna\Queue\Build\Backup;
use h4kuna\Queue\Config;
use h4kuna\Queue\Exceptions;
use h4kuna\Queue\SystemV\MsgInterface;

final class Consumer
{

	public function __construct(private Backup $backup, private MsgInterface $msg)
	{
	}


	/**
	 * @throws Exceptions\ReceiveException
	 */
	public function receive(int $messageType = Config::TYPE_DEFAULT): Message
	{
		return $this->read($messageType, 0);
	}


	/**
	 * @throws Exceptions\ReceiveException
	 */
	public function tryReceive(int $messageType = Config::TYPE_DEFAULT): ?Message
	{
		return $this->read($messageType, MSG_IPC_NOWAIT);
	}


	/**
	 * @return ($flags is 0 ? Message: Message|null)
	 * @throws Exceptions\ReceiveException
	 */
	private function read(int $messageType, int $flags): ?Message
	{
		$internalMessage = $this->msg->receive($messageType, $flags);

		if ($internalMessage === null) {
			return null;
		}

		$this->backup->remove($internalMessage);

		return $internalMessage->createMessage();
	}


	public function flush(): void
	{
		while ($this->tryReceive() !== null) {
			// empty
		}
		$this->msg->remove();
	}

}
