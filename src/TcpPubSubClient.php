<?php

namespace PubSub;

class TcpPubSubClient implements PubSubClient
{
    private $addr;
    private $port;

    public function __construct(string $addr = "127.0.0.1", $port = 8080)
    {
        $this->addr = $addr;
        $this->port = $port;
    }

    public function publish(string $topic, string $data)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new \Exception("Socket creation failed: " . socket_strerror(socket_last_error()));
        }

        $result = socket_connect($socket, $this->addr, $this->port);
        if ($result === false) {
            throw new \Exception("Socket connection failed: ($result) " . socket_strerror(socket_last_error($socket)));
        }

        $msg = new TcpPubSubMessage($topic, $data);
        socket_write($socket, $msg, strlen($msg));
        socket_close($socket);
    }
}