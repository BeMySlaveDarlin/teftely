<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;

class CommandHelp extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $this->params['peer_id'] = $this->payload->getPeerId();
        $message = "Доступные команды:\n\n";
        foreach (self::DESCRIPTIONS as $command => $description) {
            $message .= $command . ' - ' . $description . "\n";
        }

        $this->params['message'] = $message;
    }
}
