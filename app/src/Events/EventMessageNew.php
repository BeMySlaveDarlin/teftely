<?php

declare(strict_types = 1);

namespace Teftely\Events;

class EventMessageNew extends AbstractEvent
{
    public function sendMessage(): void
    {
        if ($this->message) {
            $this->send($this->message->getMethod(), $this->message->getResponse());
        }
    }
}
