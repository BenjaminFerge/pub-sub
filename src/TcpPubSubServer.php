<?php

namespace PubSub;

use React\EventLoop\Factory;
use React\Socket\Server;
use Ramsey\Uuid\Uuid;
use React\Socket\ConnectionInterface;

class TcpPubSubServer implements PubSubServer
{
    private $queues = [];
    private $loop;
    private $socket;

    public function __construct(string $addr = "127.0.0.1", $port = 8080)
    {
        $loop = Factory::create();    
        $socket = new Server("$addr:$port", $loop);
        $this->loop = $loop;
        $this->socket = $socket;
    }

    public function subscribe(string $topic, callable $callback)
    {
        $queueId = (string)Uuid::uuid4();
        $this->queues[$queueId] = [
            $topic => $callback
        ];
        return $queueId;
    }
    
    public function unsubscribe($queueId, string $topic)
    {
        unset($this->queues[$queueId][$topic]);
    }

    public function start()
    {
        $this->socket->on('connection', function (ConnectionInterface $conn) {
            $conn->on('data', function ($data) use ($conn) {
                $msg = TcpPubSubMessage::fromJson($data);
                array_map(function($q) use ($msg) {
                    if (isset($q[$msg->topic])) {
                        $q[$msg->topic]($msg->data);
                    }
                }, $this->queues);
                $conn->close();
            });
        });
        $this->loop->run();
    }
}