<?php declare(strict_types=1);

namespace h4kuna\Queue;

interface Config
{
	public const TYPE_ALL = 0; // for Consumer, can read all types
	public const TYPE_DEFAULT = 1;

	public const NO_SERIALIZE = false; // intentionally this value is no change
}
