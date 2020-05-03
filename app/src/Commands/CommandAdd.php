<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Reaction;
use Teftely\Models\User;

class CommandAdd extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        if ($user->isAdmin()) {
            $message = "Некорректный формат добавления команды. \n";
            $message .= "Ожидается [/add Код\nОписание\nКартинка]";
            if (false !== strpos($this->payload->getPayload(), "\n")) {
                $data = explode("\n", $this->payload->getPayload());
                $command = $data[0] ?? null;
                $text = $data[1] ?? null;
                $attachment = $this->payload->getAttachment();
                if (!empty($command) && (!empty($text) || !empty($attachment))) {
                    $reaction = Reaction::findOrCreate($database, $command, [
                        'command' => $command,
                        'message' => $text,
                        'attachment' => $attachment,
                    ]);

                    $command = $reaction->getCommand();
                    $message = $command ? "Создана команда [$command]" : 'Не удалось создать команду';
                }
            }
        } else {
            $message = 'Только админ может добавлять команды';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
