<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Events\AbstractEvent;
use Teftely\Events\EventConfirmation;
use Teftely\Events\EventConsole;
use Teftely\Events\EventMessageNew;
use Teftely\Events\EventUnknown;

class Events
{
    public const EVENT_CONSOLE = 'console';
    public const EVENT_CONFIRMATION = 'confirmation';
    public const EVENT_MESSAGE_NEW = 'message_new';

    private ?object $eventData;

    public function __construct(?object $eventData = null)
    {
        $this->eventData = $eventData;
    }

    public function getData(): ?object
    {
        return $this->eventData;
    }

    public function getEvent(): AbstractEvent
    {
        if (null === $this->eventData) {
            $eventHandler = new EventUnknown();
        } else {
            switch ($this->eventData->type) {
                default:
                    $eventHandler = new EventUnknown();
                    break;
                case self::EVENT_CONSOLE:
                    $eventHandler = new EventConsole();
                    break;
                case self::EVENT_CONFIRMATION:
                    $eventHandler = new EventConfirmation();
                    break;
                case self::EVENT_MESSAGE_NEW:
                    $eventHandler = new EventMessageNew();
                    break;
            }
        }

        return $eventHandler;
    }
}
