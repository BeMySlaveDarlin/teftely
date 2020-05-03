<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Message;

class CommandLazy extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $rnd = random_int(0, 1);
        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $rnd
            ? Message::findRandom($database) :
            'Пока ты чатишься, кто-то обгоняет тебя в рейтинге';
    }

    public static function check(): bool
    {
        return random_int(0, 128) === 0;
    }
}
