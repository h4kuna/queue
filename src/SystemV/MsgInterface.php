<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemV;

use h4kuna\Queue\Exceptions;
use h4kuna\Queue\Msg\InternalMessage;

interface MsgInterface
{
	public const INFO_SETUP_UID = 'msg_perm.uid';
	public const INFO_SETUP_GID = 'msg_perm.gid';
	public const INFO_SETUP_MODE = 'msg_perm.mode';
	public const INFO_CREATE_TIME = 'msg_ctime';
	public const INFO_SEND_TIME = 'msg_stime';
	public const INFO_RECEIVE_TIME = 'msg_rtime';
	public const INFO_COUNT = 'msg_qnum';
	public const INFO_SETUP_BYTES = 'msg_qbytes';
	public const INFO_LAST_SEND_PID = 'msg_lspid';
	public const INFO_LAST_RECEIVE_PID = 'msg_lrpid';

	public const MAX_MESSAGE_SIZE = 256; // bytes, max is by system, observed 8192


	public function send(InternalMessage $internalMessage): void;


	/**
	 * @return ($flags is 0 ? InternalMessage : InternalMessage|null)
	 * @throws Exceptions\ReceiveException
	 */
	public function receive(int $messageType, int $flags): ?InternalMessage;


	/**
	 * @param array<self::INFO_SETUP_*, int> $data
	 */
	public function setup(array $data): bool;


	/**
	 * @return array<self::INFO_*, int>
	 * @throws Exceptions\QueueInfoIsUnavailableException
	 */
	public function info(): array;


	public function name(): string;


	public function remove(): bool;

}
