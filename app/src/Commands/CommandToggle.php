<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Peer;
use Teftely\Models\User;

class CommandToggle extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::get($database, (string) $this->payload->getFromId());
        $peer = Peer::getOne($database, $this->payload->getPeerId());

        if ($user->isModerator()) {
            if ($peer->isEnabled()) {
                $message = $peer->disable() ? 'Бот отключен' : 'Не удалось отключить';
            } else {
                $message = $peer->enable() ? 'Бот включен' : 'Не удалось включить';
            }
        } else {
            $message = 'Только админ или модер может переключить его';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
