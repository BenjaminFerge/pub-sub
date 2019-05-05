<?php

namespace PubSub;

interface PubSubServer
{
    public function subscribe(string $topic, callable $callback);
    public function unsubscribe($id, string $topic);
    public function start();
}