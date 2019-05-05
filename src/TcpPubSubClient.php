<?php

namespace PubSub;

use React\Socket\ConnectionInterface;
use React\EventLoop\Factory;
use React\Socket\Connector;

class TcpPubSubClient implements PubSubClient
{
    private $loop;
    private $connector;
    private $addr;
    private $port;

    public function __construct(string $addr = "127.0.0.1", $port = 8080)
    {
        $loop = Factory::create();
        $this->loop = $loop;
        $this->connector = new Connector($loop);
        $this->addr = $addr;
        $this->port = $port;
    }

    public function publish(string $topic, string $data)
    {
        $host = $this->addr . ":" .  $this->port;
        $this->connector->connect($host)->then(function (ConnectionInterface $conn) use ($topic, $data) {
            $msg = new TcpPubSubMessage($topic, $data);
            $conn->write((string)$msg);
        });
        $this->loop->run();
    }
}