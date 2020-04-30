<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\User;

class CommandAdmin extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::get($database, (string) $this->payload->getFromId());

        if ($this->payload->getPayload() === $vkConfig->get('password')) {
            $user->setAdmin();
            $message = 'Вы получили права администратора';
        } else {
            $message = 'Для получения прав администратора требуется пароль';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
