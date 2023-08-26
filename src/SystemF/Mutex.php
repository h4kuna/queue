<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

use Closure;
use h4kuna\Queue\Exceptions\AcquireException;
use SysvSemaphore;

final class Mutex
{

	private ?SysvSemaphore $handler = null;

	/**
	 * deadlock protection, in one process call twice acquire()
	 * @var array<string, bool> $acquired
	 */
	private static array $acquired = [];


	public function __construct(private string $name, private int $ftok)
	{
	}


	public function acquire(): void
	{
		$this->checkIsAlreadyAcquired();

		if (sem_acquire($this->getResource()) === false) {
			throw new AcquireException(sprintf('Can not acquire "%s".', $this->name));
		}

		self::$acquired[$this->name] = true;
	}


	public function release(): void
	{
		if (!$this->tryRelease()) {
			throw new AcquireException(sprintf('First you must acquire "%s", with message: "%s".', $this->name, error_get_last()['message'] ?? ''));
		}
	}


	private function tryRelease(): bool
	{
		$release = false;
		if (self::isAcquired($this->name) && $this->handler !== null) {
			$release = @sem_release($this->handler); // intentionally @
			if ($release) {
				unset(self::$acquired[$this->name]);
			}
		}
		return $release;
	}


	private function checkIsAlreadyAcquired(): void
	{
		if (self::isAcquired($this->name)) {
			throw new AcquireException(sprintf('Mutex "%s" is already acquired.', $this->name));
		}
	}


	/**
	 * @template T
	 * @param Closure(): T $callback
	 * @return T
	 */
	public function synchronized(Closure $callback)
	{
		$this->acquire();
		try {
			return $callback();
		} finally {
			$this->release();
		}
	}


	private function getResource(): SysvSemaphore
	{
		if ($this->handler === null) {
			$this->handler = $this->createResource();
		}
		return $this->handler;
	}


	private function createResource(): SysvSemaphore
	{
		$handler = sem_get($this->ftok);
		if ($handler === false) {
			throw new AcquireException(sprintf('Can not get semaphore "%s".', $this->name));
		}
		return $handler;
	}


	private static function isAcquired(string $name): bool
	{
		return self::$acquired[$name] ?? false;
	}

}
