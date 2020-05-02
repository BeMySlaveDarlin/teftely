<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Components\Message;
use Teftely\Components\Payload;

abstract class Command
{
    public const COMMAND_HELP = '/help';
    public const COMMAND_EVENTS = '/events';
    public const COMMAND_STATUS = '/status';
    public const COMMAND_TOGGLE = '/toggle';
    public const COMMAND_SUBSCRIBE = '/sub_event';
    public const COMMAND_UNSUBSCRIBE = '/unsub_event';
    public const COMMAND_ADD_EVENT = '/add_event';
    public const COMMAND_DEL_EVENT = '/del_event';
    public const COMMAND_ADMIN = '/admin';
    public const COMMAND_MODER = '/moder';
    public const COMMAND_OBSCENE = '/obscene';
    public const COMMAND_LAZY = '/lazy';
    public const COMMAND_SORRY = '/sorry';

    public const COMMANDS = [
        self::COMMAND_HELP => CommandHelp::class,
        self::COMMAND_EVENTS => CommandEvents::class,
        self::COMMAND_STATUS => CommandStatus::class,
        self::COMMAND_TOGGLE => CommandToggle::class,
        self::COMMAND_SUBSCRIBE => CommandSubscribe::class,
        self::COMMAND_UNSUBSCRIBE => CommandUnsubscribe::class,
        self::COMMAND_ADD_EVENT => CommandAddEvent::class,
        self::COMMAND_DEL_EVENT => CommandDelEvent::class,
        self::COMMAND_ADMIN => CommandAdmin::class,
        self::COMMAND_MODER => CommandModer::class,
        self::COMMAND_OBSCENE => CommandObscene::class,
        self::COMMAND_LAZY => CommandLazy::class,
        self::COMMAND_SORRY => CommandSorry::class,
    ];

    public const DESCRIPTIONS = [
        self::COMMAND_HELP => 'доступные команды',
        self::COMMAND_EVENTS => 'список событий',
        self::COMMAND_STATUS => 'статус бота',
        self::COMMAND_TOGGLE => 'включить/отключить бота',
        self::COMMAND_SUBSCRIBE => 'подписаться на событие <ID>',
        self::COMMAND_UNSUBSCRIBE => 'отписаться от события <ID>',
        self::COMMAND_ADD_EVENT => 'новое событие HH:mm"Название"Описание. Пример: /add_event 08:00"Парам-пам"Пора вставать и унывать',
        self::COMMAND_DEL_EVENT => 'удалить событие <ID>',
    ];

    protected Payload $payload;
    protected array $params = [];

    abstract public function run(Config $vkConfig, Database $database): void;

    public static function getCommand(Config $vkConfig, Database $database, object $request): ?Command
    {
        $delimiter = ' ';
        $message = $request->object->message ?? null;

        $messageText = $message->text ?? null;
        $messageTextParts = is_string($messageText) ? explode($delimiter, $messageText) : [];
        $slashed = array_shift($messageTextParts);
        $payload = implode($delimiter, $messageTextParts);

        $commandClass = null;
        if (isset(self::COMMANDS[$slashed])) {
            $commandClass = self::COMMANDS[$slashed];
        } else {
            $hasObscene = CommandObscene::check($messageText);
            $isLazy = CommandLazy::check();
            if ($hasObscene) {
                $commandClass = self::COMMANDS[self::COMMAND_OBSCENE];
            } elseif ($isLazy) {
                $commandClass = self::COMMANDS[self::COMMAND_LAZY];
            }
        }

        if ($commandClass !== null) {
            /** @var Command $command */
            $command = new $commandClass();
            $command->setPayload($request, $payload);
            $command->run($vkConfig, $database);

            return $command;
        }

        return null;
    }

    public function getMethod(): string
    {
        return Message::METHOD_SEND;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setPayload(object $request, string $payload): void
    {
        $this->payload = new Payload($request, $payload);
    }
}
