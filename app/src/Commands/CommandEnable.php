<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Reaction;
use Teftely\Models\User;

class CommandEnable extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        if ($user->isAdmin()) {
            $reaction = Reaction::findOne($database, $this->payload->getPayload());
            if (null === $reaction) {
                $message = "Команда [$this->payload->getPayload()] не существует";
            } else {
                $message = $reaction->enable()
                    ? "Команда [{$reaction->getCommand()}] включена"
                    : "Команда [{$reaction->getCommand()}] не включена";
            }
        } else {
            $message = 'Только админ может включать команды';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
