<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\User;

class CommandModer extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        if ($this->payload->getPayload() === $vkConfig->get('password')) {
            $user->setModerator();
            $message = 'Вы получили права модератора';
        } else {
            $message = 'Для получения прав модератора требуется пароль';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
