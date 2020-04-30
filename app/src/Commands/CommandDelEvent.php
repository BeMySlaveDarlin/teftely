<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Event;
use Teftely\Models\User;

class CommandDelEvent extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::get($database, (string) $this->payload->getFromId());

        if ($user->isAdmin()) {
            $event = Event::getOne($database, $this->payload->getPayload());
            $message = $event->delete()
                ? "Событие #{$event->getId()} удалено"
                : "Событие #{$event->getId()} не удалено";
        } else {
            $message = 'Только админ может удалять события';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
