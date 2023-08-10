# Queue

This use native php queue [msg_* functions](https://www.php.net/manual/en/function.msg-get-queue.php). Keep default
parameters and does not support native serialize, message is only string.

> The queue does not survive the server restart.

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
$queue->consumer()->tryReceive() === NULL // non blocking read
$queue->consumer()->receive(2)->message === 'Hello'
```

Or read all types

```php
$queue->producer()->send('Hello', 2);
$queue->consumer()->receive(Queue\Config::TYPE_ALL)->message === 'Hello'
```

## Limitations
- queue size is 120
- the consumer can be only one, if queue failed

## Catch receive error

```php
use h4kuna\Queue;

/** @var Queue\QueueFactory $queue */
$queueFactory = new Queue\QueueFactory();

/** @var Queue\Queue $queue */
$queue = $queueFactory->create('my-queue');

tryAgain:
$queue->restore();
try {
    $message = $queue->consumer()->receive();
    // ... 
} catch (Queue\ReceiveException $e) {
    // log error
    sleep(1); // wait and try again, for less CPU usage and log spam
    goto tryAgain;
}


```

Error codes for receive:

- [base errors](https://github.com/torvalds/linux/blob/master/include/uapi/asm-generic/errno-base.h)
- [extends error](https://github.com/torvalds/linux/blob/master/include/uapi/asm-generic/errno.h)
