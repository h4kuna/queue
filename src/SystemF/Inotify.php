<?php declare(strict_types=1);

namespace h4kuna\Queue\SystemF;

use h4kuna\Dir\Dir;
use h4kuna\Queue\Exceptions\InvalidStateException;

final class Inotify
{
	/** @var resource */
	private $resource;


	public function __construct(private Dir $dir)
	{
		$this->resource = inotify_init();
	}


	public function wait(): void
	{
		$watchDescriptor = inotify_add_watch($this->resource, $this->dir->getDir(), IN_MOVED_TO);
		if ($watchDescriptor === false) {
			throw new InvalidStateException(sprintf('Inotify watch failed for dir "%s".', $this->dir->getDir()));
		}
		inotify_read($this->resource);
		inotify_rm_watch($this->resource, $watchDescriptor);
	}

}
