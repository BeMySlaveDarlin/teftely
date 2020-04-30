<?php

declare(strict_types = 1);

namespace Teftely\Components;

class EventManager
{
    public const EVENT_CONFIRMATION = 'confirmation';
    public const EVENT_MESSAGE_NEW = 'message_new';

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getResponse(?object $eventData = null): string
    {
        if (null === $eventData) {
            return 'Undefined event';
        }

        if ($eventData->type === self::EVENT_CONFIRMATION) {
            $config = $this->config->get(Config::VK_CONFIG);

            return (string) $config->get('confirmation');
        }

        return 'OK';
    }

    public function createMessage(?object $eventData = null): void
    {
        $secret = $eventData->secret ?? null;
        if ($this->config->get(Config::SECRET) === $secret) {
            $message = new Message($eventData);
            $packedMessage = self::pack($message);
            exec("php /app/bin/send $packedMessage > /dev/null 2>/dev/null &");
        }
    }

    public static function pack($input): string
    {
        return base64_encode(serialize($input));
    }

    public static function unpack($input)
    {
        return unserialize(base64_decode($input), [false]);
    }
}
