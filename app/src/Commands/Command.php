<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Components\Message;
use Teftely\Components\Payload;
use Teftely\Components\Response;
use Teftely\Models\User;

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
    public const COMMAND_FORTUNE = '/fortune';

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
        self::COMMAND_FORTUNE => CommandFortune::class,
    ];

    public const DESCRIPTIONS = [
        self::COMMAND_HELP => 'доступные команды',
        self::COMMAND_EVENTS => 'список событий',
        self::COMMAND_STATUS => 'статус бота',
        self::COMMAND_TOGGLE => 'включить/отключить бота',
        self::COMMAND_SUBSCRIBE => 'подписаться на событие <ID>',
        self::COMMAND_UNSUBSCRIBE => 'отписаться от события <ID>',
        self::COMMAND_ADD_EVENT => 'добавить событие',
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

        $isCommand = false;
        $commandClass = null;
        if (isset(self::COMMANDS[$slashed])) {
            $commandClass = self::COMMANDS[$slashed];
            $isCommand = true;
        } else {
            $isLazy = CommandLazy::check();
            $isFortune = CommandFortune::check($messageText);
            $hasObscene = CommandObscene::check($messageText);
            if ($isFortune) {
                $commandClass = self::COMMANDS[self::COMMAND_FORTUNE];
            } elseif ($isLazy) {
                $commandClass = self::COMMANDS[self::COMMAND_LAZY];
            } elseif ($hasObscene) {
                $commandClass = self::COMMANDS[self::COMMAND_OBSCENE];
            }
        }
        if (is_string($messageText) && !empty($messageText)) {
            self::saveMessage($vkConfig, $database, $request, $messageText, $isCommand);
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

    public static function saveMessage(
        Config $vkConfig,
        Database $database,
        object $request,
        string $text,
        $commandClass = false
    ): void {
        try {
            $payload = new Payload($request);
            $user = User::findOne($database, $payload->getFromId());
            if (null === $user) {
                $response = new Response();
                $jsonResponse = $response->send(
                    $vkConfig,
                    Message::METHOD_USERS_GET,
                    ['user_ids' => $payload->getFromId()]
                );

                $user = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
                $fullName = null;
                if (!empty($user['response'][0]['first_name'])) {
                    $fullName = trim($user['response'][0]['first_name'] . ' ' . ($user['response'][0]['last_name'] ?? null));
                }
                $user = User::createOne($database, $payload->getFromId(), $fullName);
            }
            if (false === $commandClass) {
                $user->saveMessage($payload->getPeerId(), $text);
            }
        } catch (\Throwable $throwable) {
            throw $throwable;
        }
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
