<?php declare(strict_types=1);

namespace h4kuna\Queue;

interface Config
{
	public const TYPE_ALL = 0; // for Consumer, can read all types
	public const TYPE_DEFAULT = 1;

	public const NO_SERIALIZE = false; // intentionally this value is no change

	public const QUEUE_ERROR = 43;
	public const QUEUE_IS_FULL = 11;

	public const MINIMAL_QUEUE_SIZE = 28;
}
