<?php declare(strict_types=1);

namespace h4kuna\Queue\Tests\Sandbox;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MyMessageHandler extends HandlerDescriptor
{

}
