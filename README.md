# RabbitMQ RPC message 

This package makes ready to use implementation of RabbitMQ RPC tutorial published on:
https://www.rabbitmq.com/tutorials/tutorial-six-php.html

## Installation
Install package by Composer:
```php
composer require tzmudz/rabbitmq_rpc
```
Package use a *Environment Variables* delivered by `vlucas/phpdotenv` package. So 
you have to create an `.env` file in root folder containing variables:

```dotenv
RABBTIMQ_PORT=5672
RABBTIMQ_HOST=localhost
RABBTIMQ_USERNAME=guest
RABBTIMQ_PASSWORD=guest
```

Thus if you are using e.g. Laravel all you have to do is to put this variables in your `.env` 
app file.

**Notice** If your application don't use `.env` files, don't forget to load them by executing the code:
```php
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
```
More information you can find here: https://github.com/vlucas/phpdotenv

## Using
Package comes with two classes: `Producer` and `Consumer`. 

#### Producer
The `Producer` class is responsible to deliver the message to RabbtiMQ. 

In your application 
create a new instance of `Tzm\Rpc\Producer()`. As a parameter you can set the queue name 
(by default the name is `default`):

```php
$producer = new Tzm\Rpc\Producer('long_task_queue_name');
``` 

Otherwise you can set the queue name by calling the 
`setQueueName()`. 

```php
$producer = new Tzm\Rpc\Producer();
$producer->setQueueName('long_task_queue_name');
```

Finally you have to call the method `call()` with message as parameter:

```php
$producer = new Tzm\Rpc\Producer();
$producer->setQueueName('long_task_queue_name')->call($message);
```

Where `$message` is a **string** with data that should be send to the RabbtiMQ server. 

Actually RPC is used for send a message and wait for the response given from server. But, if you don't 
want to wait for server response you can use `withoutWaiting()`:
```php
$producet = new Tzm\Rpc\Producer();
$producer->withoutWaiting()->call($message);
```

#### Consumer
To process the queued message you need `Consumer` class. This class is abstract. 

You have to create a new class inherits from `Consumer` abstract class. Then you have to implement 
several methods.

**`handleMessage()`** - this method is responsible for process the message. It accepts `$message` as 
a parameter e.g.
```php
public function handleMessage($message)
{
    echo $message;
}
```
**`handleError()`** - it's not necessary but it handle the error. If the method `handleMessage()` thrown an
error it will be pass to the `errorHandler()` method. So you can do with this error what ever you want. 
e.g. in Laravel you can log the error message:
```php
public function handleError(\Exception $e)
{
    Log::error($e->getMessage());
}
``` 

After the message is handled RabbitMQ returns response message. Initially


This class as `Producer` also have a `setQueueName()` method. 


**ATTENTION!**
Don't forget to run Consumer class first (it will create the queue on RabbitMQ server)

## License 
MIT



