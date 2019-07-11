<?php

use PHPUnit\Framework\TestCase;
use PubSub\AmqpPubSubServer;
use PubSub\AmqpPubSubClient;

class AmqpPubSubTest extends TestCase
{
    private $server;
    private $client;

    public function setUp() : void
    {
        $exchange = "test";
        $this->server = new AmqpPubSubServer($exchange);
        $this->client = new AmqpPubSubClient($exchange);
    }

    public function testCanHandleMultipleTopic()
    {
        $topic1 = 'onStart';
        $data1 = 'data 1';

        $topic2 = 'onStop';
        $data2 = 'data 2';

        $this->server->subscribe($topic1, function($msg) use ($data1) {
            $this->assertEquals($msg->getBody(), $data1);
        });


        $this->server->subscribe($topic2, function($msg) use ($data2) {
            $this->assertEquals($msg->getBody(), $data2);
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