<?php

namespace PubSub;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpPubSubClient implements PubSubClient
{
    private $connection;
    private $channel;
    private $exchange;

    public function __construct($exchange, $addr = "localhost", $port = 5672, $user = "guest", $pwd = "guest") {
        $this->exchange = $exchange;
        $connection = new AMQPStreamConnection($addr, $port, $user, $pwd);
        $this->connection = $connection;
        $channel = $connection->channel();
        $channel->exchange_declare($exchange, "direct", false, false, false);
        $this->channel = $channel;
    }

    public function publish($topic, string $data)
    {
        $msg = new AMQPMessage($data, [
            "delivery_mode" => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $this->channel->basic_publish($msg, $this->exchange, $topic);
    }
}
