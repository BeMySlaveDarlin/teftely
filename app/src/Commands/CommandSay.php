<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;

class CommandSay extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $rnd = random_int(0, 100);
        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $rnd === 0 ? 'лень' : $this->payload->getPayload();
    }
}
