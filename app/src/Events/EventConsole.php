<?php

declare(strict_types = 1);

namespace Teftely\Events;

class EventConsole extends AbstractEvent
{
    public function sendMessage(): void
    {
        if (PHP_SAPI === 'cli') {
        }
    }

    public function getResponseData(): ?string
    {
        if (PHP_SAPI !== 'cli') {
            return 'Unsupported PHP_SAPI';
        }

        return 'OK' . PHP_EOL;
    }
}
