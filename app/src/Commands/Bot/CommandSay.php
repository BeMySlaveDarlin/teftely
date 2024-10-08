<?php

declare(strict_types = 1);

namespace Teftely\Commands\Bot;

use Teftely\Commands\Command;
use Teftely\Components\Config;
use Teftely\Components\Database;

class CommandSay extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $rnd = random_int(0, 100);
        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $rnd === 0 ? 'чот лень повторять' : $this->payload->getPayload();
    }
}
