<?php

declare(strict_types = 1);

namespace Teftely\Events;

class EventConfirmation extends AbstractEvent
{
    public function getResponseData(): ?string
    {
        return $this->config->get('confirmation');
    }
}
