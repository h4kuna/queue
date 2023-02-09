<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');
Tester\Environment::setup();
Tracy\Debugger::enable(false, __DIR__ . '/temp');
