# Queue

This use native php queue [msg_* functions](https://www.php.net/manual/en/function.msg-get-queue.php).

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

This is not server, it can run as only instance on current computer.

## Catch receive error

The queue has backup implementation by [Backup](src/Backup/Filesystem.php).

```php
use h4kuna\Queue;

/** @var Queue\QueueFactory $queue */
$queueFactory = new Queue\QueueFactory();

/** @var Queue\Queue $queue */
$queue = $queueFactory->create('my-queue');

$queue->restore(); // restore from backup, after restart
do {
    try {
        $message = $queue->consumer()->receive();
        // ... 
    } catch (Queue\ReceiveException $e) {
        // log error
        // stop process if code is 22
    }
} while(true);
```

### List of queues
```bash
ipcs -q
```

Error codes for receive:

- [base errors](https://github.com/torvalds/linux/blob/master/include/uapi/asm-generic/errno-base.h)
- [extends error](https://github.com/torvalds/linux/blob/master/include/uapi/asm-generic/errno.h)
