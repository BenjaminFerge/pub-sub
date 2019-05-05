<?php

namespace PubSub;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

interface PubSubClient
{
    public function publish(string $topic, string $data);
}
