<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Peer;

class CommandStatus extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $peer = Peer::getOne($database, $this->payload->getPeerId());

        $message = $peer->isEnabled() ? 'Бот включен' : 'Бот отключен';

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
