<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Events\AbstractEvent;

class Response
{
    private ?AbstractEvent $eventHandler = null;

    public function __construct(?AbstractEvent $eventHandler = null)
    {
        $this->eventHandler = $eventHandler;
    }

    public function setEventHandler(?AbstractEvent $eventHandler): void
    {
        $this->eventHandler = $eventHandler;
    }

    public function send(): string
    {
        $response = 'OK';
        if ($this->eventHandler instanceof AbstractEvent) {
            $this->eventHandler->sendMessage();
            $response = $this->eventHandler->getResponseData();
        }

        return $response;
    }
}
