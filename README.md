# Queue

This use native php queue [msg_* functions](https://www.php.net/manual/en/function.msg-get-queue.php). Keep default parameters and does not support native serialize, message is only string.

### Installation by composer
`composer require h4kuna/queue`

### How to use

Base is [QueueFactory](src/QueueFactory.php).

```php
use h4kuna\Queue;

/** @var Queue\QueueFactory $queue */
$queueFactory = new Queue\QueueFactory();

/** @var Queue\Queue $queue */
$queue = $queueFactory->create('my-queue');

$queue->producer()->send('Hello');

$queue->consumer()->receive()->message === 'Hello'
```

The messages can to have different types.

```php
$queue->producer()->send('Hello', 2);
$queue->consumer()->tryReceive()->message === NULL // non blocking read
$queue->consumer()->receive(2)->message === 'Hello'
```

Or read all types
```php
$queue->producer()->send('Hello', 2);
$queue->consumer()->receive(Queue\Config::TYPE_ALL)->message === 'Hello'
```

