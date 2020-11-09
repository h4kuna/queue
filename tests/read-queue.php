<?php declare(strict_types=1);

use h4kuna\Queue;

require __DIR__ . '/../vendor/autoload.php';

$argv = $_SERVER['argv'];

$name = $argv[1] ? strval($argv[1]) : die('string name');
$verbose = isset($argv[2]) ? boolval($argv[2]) : false;

$output = function (string $message) use ($verbose) {
	if ($verbose) {
		echo $message . PHP_EOL;
	}
};
$start = microtime(true);
$output(sprintf('Start wait.'));
$queue = (new Queue\QueueFactory())
	->create($name);
$message = $queue
	->consumer()
	->receive(0);

$end = microtime(true);
$output(sprintf('End sleep with time %s.', $end - $start));
$output(sprintf('Type: "%s", Message: "%s", Count: "%s"', $message->type, $message->message, $queue->inQueue()));

return 0;
