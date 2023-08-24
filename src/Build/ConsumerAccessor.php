<?php declare(strict_types=1);

namespace h4kuna\Queue\Build;

use h4kuna\Queue\Msg\Consumer;

interface ConsumerAccessor
{
	function get(): Consumer;
}
