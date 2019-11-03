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

## Usage
Package comes with two classes: `Producer` and `Consumer`. 

### Producer
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

### Consumer
To process the queued message you need `Consumer` class. This class is abstract. 

You have to create a new class inherits from `Consumer` abstract class. Then you have to implement 
several methods.

Just like class `Producer` also have a `setQueueName()` method. 

#### handleMessage()
This method is responsible for process the message. It accepts `$message` as 
a parameter e.g.
```php
public function handleMessage($message)
{
    echo $message;
}
```
#### handleError()
Literally it's not necessary, but it handle the error. If the method `handleMessage()` thrown an
error it will be pass to the `errorHandler()` method. So you can do with this error what ever you want. 
e.g. in Laravel you can log the error message:
```php
public function handleError(\Exception $e)
{
    Log::error($e->getMessage());
}
``` 
#### setResults()
After the message is handled RabbitMQ returns response message. Initially it returns true. But if you want 
you can use `setResult()` method to set the data you want to return. E.g.:
```php
public function handleMessage($message)
{
    $this->setResult('this_will_be_returned_to_Producer');
}
```

#### Run Consumer

You inherit class of `Consumer` should look like this:
```php
namespace Tzm\Rpc;

class EchoMessage extends Consumer
{
    protected function handleMessage($message)
    {
        echo $message;
    }

    protected function handleError(\Exception $e)
    {
        \Log::error($e->getMessage()); // In Laravel Log exception message
    }
}
```
Then you can run the Consumer:
```php
$consumer = new EchoMessage();
$consumer->run();
```
#### Console information
Additionally there are two methods:
##### consoleMessage($req) 
It's used to display message in console. It has one parameter `$req` - containing all info about recievied 
message. By default this method returns string: `New request` so if new message will be delivered 
the console should print: 
```
[03.11 20:57:02] New request === Task Starts ===
[03.11 20:57:05] New request === Task Ends ===
```
Whole message could be change by overriding `consoleInfo()` method.

**ATTENTION!**
Don't forget to run Consumer class first (it will create the queue on RabbitMQ server)

## License 
MIT