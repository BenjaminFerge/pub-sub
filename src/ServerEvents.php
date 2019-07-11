<?php

namespace PubSub;

trait ServerEvents
{
    private $eventHandlers = [];
    
    private function handleEvent($e)
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