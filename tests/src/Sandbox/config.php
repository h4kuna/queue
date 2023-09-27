<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Sandbox;

require __DIR__ . '/../../../vendor/autoload.php';

use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;





$handler = new HandlerDescriptor();

return new MessageBus([
	new HandleMessageMiddleware(new HandlersLocator([
		MyMessage::class => [$handler],
	])),
]);
