<?php

declare(strict_types = 1);

namespace Teftely\Commands\Reactions;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Peer;
use Teftely\Models\Reaction;
use Teftely\Models\User;

class CommandReactions extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $message = 'Нет доступных пользовательских команд';
        $user = User::findOrCreate($database, $this->payload->getFromId());
        $peer = Peer::findOrCreate($database, $this->payload->getPeerId());
        $reactions = Reaction::findList($database, $user->isAdmin() ? null : Reaction::STATUS_ACTIVE);
        if (!empty($reactions) && $peer->isEnabled()) {
            $message = "Список пользовательских команд:\n\n";
            foreach ($reactions as $reaction) {
                $message .= "Команда: {$reaction->getCommand()}\n";
                $message .= "Статус: {$reaction->getStatus()}\n";
                $message .= "Сообщение: {$reaction->getMessage(null)}\n";
                $message .= "Есть вложение? {$reaction->hasAttachment()}\n\n";
            }
        }

        $this->params['message'] = $message;
        $this->params['peer_id'] = $this->payload->getPeerId();
    }
}
