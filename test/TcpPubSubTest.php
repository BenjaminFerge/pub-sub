<?php

use PHPUnit\Framework\TestCase;
use PubSub\TcpPubSubServer;
use PubSub\TcpPubSubClient;

class TcpPubSubTest extends TestCase
{
    private $server;
    private $client;

    public function setUp() : void
    {
        $this->server = new TcpPubSubServer();
        $this->client = new TcpPubSubClient();
    }

    public function testCanHandleMultipleTopic()
    {
        $topic1 = 'onStart';
        $data1 = 'data 1';

        $topic2 = 'onStop';
        $data2 = 'data 2';

        $this->server->subscribe($topic1, function($msg) use ($data1) {
            $this->assertEquals($msg, $data1);
        });


        $this->server->subscribe($topic2, function($msg) use ($data2) {
            $this->assertEquals($msg, $data2);
            $this->server->stop();
        });
        
        $onStart = function() use ($topic1, $data1, $topic2, $data2) {
            $this->client->publish($topic1, $data1);
            $this->client->publish($topic2, $data2);
        };


        $this->server->onStart($onStart);
        $this->server->start();
    }
}