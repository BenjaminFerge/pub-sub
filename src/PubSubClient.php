<?php

namespace PubSub;

abstract class PubSubClient
{
    abstract public function publish(string $topic, string $data);
}
