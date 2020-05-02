<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Commands\Command;

class Message
{
    public const METHOD_SEND = 'messages.send';
    public const METHOD_USERS_GET = 'users.get';

    public object $request;

    public function __construct(object $request)
    {
        $this->request = $request;
    }

    public function send(Config $vkConfig, Database $database, Response $response): ?string
    {
        $command = Command::getCommand($vkConfig, $database, $this->request);
        if (null === $command) {
            return null;
        }
        $method = $command->getMethod();
        $params = $command->getParams();

        return $response->send($vkConfig, $method, $params);
    }
}
