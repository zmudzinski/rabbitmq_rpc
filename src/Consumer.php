<?php


namespace Tzm\Rpc;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Consumer
{
    /**
     * @var string
     */
    private $queueName;
    private $result;


    /**
     * Consumer constructor.
     * @param string $queueName
     * @param $handleClass
     * @throws \Exception
     */
    public function __construct(string $queueName = 'default')
    {
        $this->queueName = $queueName;
    }

    /**
     * Set the queue name
     *
     * @param string $queueName
     * @return $this
     */
    public function setQueueName(string $queueName)
    {
        $this->queueName = $queueName;
        return $this;
    }

    /**
     * Get the message from Queue
     *
     * @param string $message
     * @return mixed
     */
    abstract protected function handleMessage($message);

    /**
     * Handle the error from handle message method
     *
     * @param \Exception $e
     * @return mixed
     */
    abstract protected function handleError(\Exception $e);

    /**
     * Return the result of operation
     *
     * @return mixed
     */
    protected function getResult()
    {
        return $this->result ?? true;
    }

    /**
     * Set the result of operation
     *
     * @param string $result
     * @return mixed
     */
    protected function setResult(string $result)
    {
        $this->result = $result;
    }

    /**
     * Message displayed when request received
     *
     * @param $req
     * @param string|null $additional
     * @return string
     * @throws \Exception
     */
    protected function consoleInfo($req, string $additional = null)
    {
        $date = new \DateTime();
        echo sprintf(" [%s] %s %s\n", $date->format('d.m H:i:s'), $additional, $this->consoleMessage($req));
    }

    /**
     * Custom message for console info
     *
     * @param $req
     * @return string
     */
    protected function consoleMessage($req)
    {
        return "New request";
    }

    /**
     * Run worker
     *
     * @throws \ErrorException
     */
    public function run()
    {
        $connection = new AMQPStreamConnection(
            getenv('RABBTIMQ_HOST'),
            getenv('RABBTIMQ_PORT'),
            getenv('RABBTIMQ_USERNAME'),
            getenv('RABBTIMQ_PASSWORD')
        );

        $channel = $connection->channel();

        $channel->queue_declare($this->queueName, false, true, false, false);

        echo "[x] Awaiting RPC requests (Queue: {$this->queueName})\n";

        $callback = function ($req) {

            echo $this->consoleInfo($req, '[NEW]');

            try {
                $this->handleMessage($req->body);
            } catch (\Exception $e) {
                echo $this->consoleInfo($req, '[ERR]');
                $this->handleError($e);
            }

            echo $this->consoleInfo($req, '[OK]');

            $msg = new AMQPMessage($this->getResult(), array('correlation_id' => $req->get('correlation_id')));

            $req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to'));
            $req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($this->queueName, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

}




