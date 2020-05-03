<?php

declare(strict_types = 1);

namespace Teftely\Commands\Reactions;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Reaction;
use Teftely\Models\User;

class CommandDel extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        if ($user->isAdmin()) {
            $reaction = Reaction::findOne($database, $this->payload->getPayload());
            if (null === $reaction) {
                $message = "Команда [{$this->payload->getPayload()}] не существует";
            } else {
                $message = $reaction->delete()
                    ? "Команда [{$reaction->getCommand()}] удалена"
                    : "Команда [{$reaction->getCommand()}] не удалена";
            }
        } else {
            $message = 'Только админ может удалять команды';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
