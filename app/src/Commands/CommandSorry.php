<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;

class CommandSorry extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = 'Извените, йа сломалса.';
    }
}
