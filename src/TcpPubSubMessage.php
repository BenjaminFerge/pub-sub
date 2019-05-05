<?php

namespace PubSub;

class TcpPubSubMessage
{
    public $topic;
    public $data;

    public function __construct($topic, $data)
    {
        $this->topic = $topic;
        $this->data = $data;
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function toJson()
    {
        return json_encode([
            $this->topic,
            $this->data
        ]);
    }

    public static function fromJson($json)
    {
        list($topic, $data) = json_decode($json, true);
        return new self($topic, $data);
    }
}