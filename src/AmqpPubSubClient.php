<?php

namespace PubSub;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpPubSubClient implements PubSubClient
{
    private $connection;
    private $channel;
    private $exchange;
    private $cbQueue;
    private $corrId;
    private $resp;

    public function __construct($exchange, $addr = "localhost", $port = 5672, $user = "guest", $pwd = "guest") {
        $this->exchange = $exchange;
        $connection = new AMQPStreamConnection($addr, $port, $user, $pwd);
        $this->connection = $connection;
        $channel = $connection->channel();
        $channel->exchange_declare($exchange, "direct", false, false, false);
        $this->channel = $channel;
        list($this->cbQueue, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );
        $this->channel->basic_consume(
            $this->cbQueue,
            '',
            false,
            true,
            false,
            false,
            array(
                $this,
                'onResponse'
            )
        );
    }

    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corrId) {
            $this->resp = $rep->body;
        }
    }

    public function publish($topic, string $data)
    {
        $this->resp = null;
        $this->corrId = uniqid();

        $msg = new AMQPMessage($data, [
            "delivery_mode" => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            "correlation_id" => $this->corrId,
            "reply_to" => $this->cbQueue
        ]);

        $this->channel->basic_publish($msg, $this->exchange, $topic);
        while (!$this->resp) {
            $this->channel->wait();
        }
        return $this->resp;
    }
}
