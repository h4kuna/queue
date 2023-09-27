<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

use Generator;
use h4kuna\DataType\Iterators\ActiveWait;
use h4kuna\Dir\Dir;
use h4kuna\Queue\Config;
use h4kuna\Queue\MessageQueue;
use h4kuna\Queue\Msg\InternalMessage;
use h4kuna\Queue\Utils\ScanDir;

final class Msg implements MessageQueue
{
	public function __construct(
		private int $permission,
		private Dir $dir,
		private Dir $tempDir,
		private ActiveWait $activeWait,
		private Lock $mutex,
		private ?Inotify $inotify = null,
	)
	{
	}


	public function send(InternalMessage $internalMessage): void
	{
		$file = $this->tempDir->filename($internalMessage->id);
		file_put_contents($file, $internalMessage->serialized());
		chmod($file, $this->permission);
		rename($file, $this->dir->filename($internalMessage->id));
	}


	public function receive(int $messageType, int $flags): ?InternalMessage
	{
		if (($flags & 1) !== 0) {
			return $this->findMessage($messageType);
		}
		$message = null;
		$this->activeWait->run(function () use ($messageType, &$message) {
			if ($this->inotify !== null && ScanDir::content($this->dir) === []) {
				$this->inotify->wait();
			}

			$message = $this->findMessage($messageType);
			return $message !== null;
		});

		return $message;
	}


	public function name(): string
	{
		return basename($this->dir->getDir());
	}


	public function remove(): bool
	{
		foreach ($this->source() as $file) {
			@unlink($file);
		}

		return true;
	}


	public function setup(array $data): bool
	{
		throw new \RuntimeException('not implemented');
	}


	public function info(): array
	{
		$create = filectime($this->dir->getDir());
		$receive = fileatime($this->dir->getDir());
		$count = $send = 0;
		foreach ($this->source() as $file) {
			if ($count === 0) {
				$send = filectime($file);
			} else {
				$createTime = filectime($file);
				if ($createTime > $send) {
					$send = $createTime;
				}
			}
			++$count;
		}
		assert(is_int($send) && is_int($create) && is_int($receive));

		return [
			self::INFO_SETUP_UID => -1,
			self::INFO_SETUP_GID => -1,
			self::INFO_SETUP_MODE => $this->permission,
			self::INFO_CREATE_TIME => $create,
			self::INFO_SEND_TIME => $send,
			self::INFO_RECEIVE_TIME => $receive,
			self::INFO_COUNT => $count,
			self::INFO_SETUP_BYTES => 0,
			self::INFO_LAST_SEND_PID => 0,
			self::INFO_LAST_RECEIVE_PID => 0,
		];
	}


	/**
	 * @return Generator<int, string>
	 */
	private function source(): Generator
	{
		foreach (ScanDir::content($this->dir) as $k => $file) {
			yield $k => $this->dir->filename($file);
		}
	}


	/**
	 * @return Generator<string, InternalMessage>
	 */
	public function read(int $messageType = Config::TYPE_ALL): Generator
	{
		foreach ($this->source() as $file) {
			$content = file_get_contents($file);
			assert(is_string($content));
			yield $file => InternalMessage::unserialize($content, $messageType);
		}
	}


	private function findMessage(int $messageType): ?InternalMessage
	{
		return $this->mutex->synchronized(function () use ($messageType): ?InternalMessage {
			foreach ($this->read($messageType) as $file => $message) {
				if ($messageType !== 0 && $messageType !== $message->type) {
					continue;
				}
				@unlink($file);
				return $message;
			}

			return null;
		});
	}

}
