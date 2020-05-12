<?php

declare(strict_types = 1);

namespace Teftely\Commands\Story;

use Teftely\Components\Config;
use Teftely\Components\Database;

class CommandStoryVoteDown extends CommandStoryVoteUp
{
    public const VOTES_REQUIRED = 2;

    public function run(Config $vkConfig, Database $database): void
    {
        $this->vote($database, false);
    }
}
