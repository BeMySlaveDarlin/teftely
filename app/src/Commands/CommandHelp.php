<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;

class CommandHelp extends Command
{
    public const DESCRIPTIONS = [
        self::COMMAND_EVENTS => 'список событий',
        self::COMMAND_EVENTS . ' <ID>' => 'конкретное событие',
        self::COMMAND_STATUS => 'статус бота',
        self::COMMAND_TOGGLE => 'включить/отключить бота',
        self::COMMAND_SUBSCRIBE . ' <ID>' => 'подписаться на событие',
        self::COMMAND_UNSUBSCRIBE . ' <ID>' => 'отписаться от события',
        self::COMMAND_ADD_EVENT => 'добавить событие',
        self::COMMAND_DEL_EVENT . ' <ID>' => 'удалить событие',
        self::COMMAND_TOP => 'топ болтушек',
        self::COMMAND_FORTUNE => 'гадалка, задай вопрос, ответ на который: ДА/НЕТ',
    ];

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
