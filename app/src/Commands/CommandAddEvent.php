<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Models\Event;
use Teftely\Models\User;

class CommandAddEvent extends Command
{
    public function run(Config $vkConfig, Database $database): void
    {
        $user = User::findOrCreate($database, $this->payload->getFromId());

        if ($user->isAdmin()) {
            $message = "Некорректный формат добавления события. \n";
            $message .= "Ожидается [/add_event Название\nОписание\nВремя в формате ЧЧ:мм\nДень недели цифрой (понедельник 1)]";
            if (false !== strpos($this->payload->getPayload(), "\n")) {
                $data = explode("\n", $this->payload->getPayload());
                $name = $data[0] ?? null;
                $text = $data[1] ?? null;
                $time = $data[2] ?? null;
                $week = $data[3] ?? null;
                if (!empty($time) && !empty($text)) {
                    $timeParts = explode(':', $time);
                    if (count($timeParts) !== 2
                        || !is_numeric($timeParts[0])
                        || !is_numeric($timeParts[1])
                        || strlen($timeParts[0]) !== 2
                        || strlen($timeParts[1]) !== 2
                    ) {
                        $time = null;
                    }

                    $event = Event::findOrCreate($database, null, [
                        'time' => $time,
                        'week' => $week,
                        'name' => $name,
                        'message' => $text,
                        'attachment' => $this->payload->getAttachment(),
                    ]);

                    $id = $event->getId();
                    $message = $id ? "Создано событие #$id" : 'Не удалось создать событие';
                }
            }
        } else {
            $message = 'Только админ может добавлять события';
        }

        $this->params['peer_id'] = $this->payload->getPeerId();
        $this->params['message'] = $message;
    }
}
