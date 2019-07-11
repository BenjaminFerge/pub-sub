<?php

namespace PubSub;

abstract class PubSubServer
{
    abstract public function subscribe(string $topic, callable $callback);
    abstract public function unsubscribe($id, string $topic);
    abstract public function start();

    protected $eventHandlers = [];
    
    protected function handleEvent($e)
    {
        if (isset($this->eventHandlers[$e])) {
            ($this->eventHandlers[$e])();
        }
    }

    public function onStart(callable $cb)
    {
        $this->eventHandlers['start'] = $cb;
    }

    public function onStop(callable $cb)
    {
        $this->eventHandlers['stop'] = $cb;
    }
}