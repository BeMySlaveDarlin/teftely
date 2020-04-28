<?php

declare(strict_types = 1);

namespace Teftely\Events;

class EventUnknown extends AbstractEvent
{
    public function getResponseData(): ?string
    {
        return "Unsupported event: $this->eventData->type";
    }
}
