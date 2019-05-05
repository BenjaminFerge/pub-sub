<?php

namespace PubSub;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpPubSubServer implements PubSubServer
{
    private $channel;
    private $connection;
    private $exchange;

    public function __construct($exchange, $addr = "localhost", $port = 5672, $user = "guest", $pwd = "guest")
    {
        $this->exchange = $exchange;
        $this->connection = new AMQPStreamConnection($addr, $port, $user, $pwd);
        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare($exchange, "direct", false, false, false);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function subscribe(string $topic, callable $callback)
    {
        list($queueId, ,) = $this->channel->queue_declare("", false, false, true, false);
        $this->channel->queue_bind($queueId, $this->exchange, $topic);
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queueId, "", false, false, false, false, function($req) use ($callback) {
            $result = $callback($req);
            $this->callback($result, $req);
        });
        return $queueId;
    }

    public function callback($result, $req)
    {
        $msg = new AMQPMessage(
            (string) $result,
            array('correlation_id' => $req->get('correlation_id'))
        );
    
        $req->delivery_info['channel']->basic_publish(
            $msg,
            '',
            $req->get('reply_to')
        );
        $req->delivery_info['channel']->basic_ack(
            $req->delivery_info['delivery_tag']
        );
    }
    
    public function unsubscribe($queueId, string $topic)
    {
        $this->channel->queue_unbind($queueId, $this->exchange, $topic);
    }

    public function start()
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->__destruct();
    }
}