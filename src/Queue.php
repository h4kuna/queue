<?php declare(strict_types=1);

namespace h4kuna\Queue;

use DateTimeImmutable;
use h4kuna\Queue\Msg\Consumer;
use h4kuna\Queue\Msg\Producer;

final class Queue
{

	public function __construct(
		private MessageQueue $msg,
		private Producer $producer,
		private Consumer $consumer,
	)
	{
	}


	public function msg(): MessageQueue
	{
		return $this->msg;
	}


	/**
	 * @return array<string, mixed>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function information(): array
	{
		$info = $this->msg->info();
		$extends = [
			MessageQueue::INFO_SETUP_MODE => Linux::permissionInToText($info[MessageQueue::INFO_SETUP_MODE]),
			MessageQueue::INFO_CREATE_TIME => self::createDateTime($info[MessageQueue::INFO_CREATE_TIME]),
			MessageQueue::INFO_SEND_TIME => self::createDateTime($info[MessageQueue::INFO_SEND_TIME]),
			MessageQueue::INFO_RECEIVE_TIME => self::createDateTime($info[MessageQueue::INFO_RECEIVE_TIME]),
			MessageQueue::INFO_SETUP_BYTES => $info[MessageQueue::INFO_SETUP_BYTES],
			MessageQueue::INFO_COUNT => $info[MessageQueue::INFO_COUNT],
			MessageQueue::INFO_LAST_RECEIVE_PID => $info[MessageQueue::INFO_LAST_RECEIVE_PID],
			MessageQueue::INFO_LAST_SEND_PID => $info[MessageQueue::INFO_LAST_SEND_PID],
		];

		[
			'user' => $extends[MessageQueue::INFO_SETUP_UID],
			'group' => $extends[MessageQueue::INFO_SETUP_GID],
		] = Linux::userGroupToText($info[MessageQueue::INFO_SETUP_UID], $info[MessageQueue::INFO_SETUP_GID]);

		return $extends;
	}


	/**
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function count(): int
	{
		return $this->msg->info()[MessageQueue::INFO_COUNT];
	}


	public function consumer(): Consumer
	{
		return $this->consumer;
	}


	public function producer(): Producer
	{
		return $this->producer;
	}


	private static function createDateTime(int $timestamp): ?DateTimeImmutable
	{
		if ($timestamp === 0) {
			return null;
		}

		return new DateTimeImmutable("@$timestamp");
	}

}
