<?php declare(strict_types=1);

namespace h4kuna\Queue\Build;

use h4kuna\Queue\Msg\Producer;

interface ProducesAccessor
{
	function get(): Producer;
}
