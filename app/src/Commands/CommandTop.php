<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Message;
use Teftely\Models\User;

class CommandTop extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $users = User::topTalkers($database, $this->payload->getPeerId());
        $total = Message::total($database, $this->payload->getPeerId());

        if (!empty($users)) {
            $message = "Топ болтушек:\n";
            foreach ($users as $index => $user) {
                $place = $index + 1;
                $rating = round($user['msg'] / $total, 2) * 100;
                $message .= "#{$place}. {$user['full_name']} - {$rating} &#11088; / {$user['msg']} &#128172;\n";
            }
        } else {
            $message = 'Нет данных';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
