<?php

declare(strict_types = 1);

namespace Teftely\Commands;

use Teftely\Commands\Bot\CommandSay;
use Teftely\Commands\Bot\CommandSorry;
use Teftely\Commands\Bot\CommandStatus;
use Teftely\Commands\Bot\CommandToggle;
use Teftely\Commands\Events\CommandAddEvent;
use Teftely\Commands\Events\CommandDelEvent;
use Teftely\Commands\Events\CommandEvents;
use Teftely\Commands\Events\CommandSubscribe;
use Teftely\Commands\Events\CommandUnsubscribe;
use Teftely\Commands\Reactions\CommandAdd;
use Teftely\Commands\Reactions\CommandDel;
use Teftely\Commands\Reactions\CommandDisable;
use Teftely\Commands\Reactions\CommandEnable;
use Teftely\Commands\Reactions\CommandReaction;
use Teftely\Commands\Reactions\CommandReactions;
use Teftely\Commands\Story\CommandStory;
use Teftely\Commands\Story\CommandStoryAdd;
use Teftely\Commands\Story\CommandStoryDel;
use Teftely\Commands\Story\CommandStoryLast;
use Teftely\Commands\Story\CommandStoryVoteDown;
use Teftely\Commands\Story\CommandStoryVoteUp;
use Teftely\Commands\Users\CommandAdmin;
use Teftely\Commands\Users\CommandModer;
use Teftely\Commands\Users\CommandTop;
use Teftely\Components\Config;
use Teftely\Components\Database;
use Teftely\Components\Message;
use Teftely\Components\Payload;
use Teftely\Components\Response;
use Teftely\Models\Reaction;
use Teftely\Models\User;
use Throwable;

abstract class Command
{
    //Bot related commands
    public const COMMAND_SAY = '/say';
    public const COMMAND_SORRY = '/sorry';
    public const COMMAND_STATUS = '/status';
    public const COMMAND_TOGGLE = '/toggle';

    //Events related commands
    public const COMMAND_EVENTS = '/events';
    public const COMMAND_ADD_EVENT = '/add_event';
    public const COMMAND_DEL_EVENT = '/del_event';
    public const COMMAND_SUBSCRIBE = '/sub_event';
    public const COMMAND_UNSUBSCRIBE = '/unsub_event';

    //Reactions related commands
    public const COMMAND_ADD = '/add';
    public const COMMAND_DEL = '/del';
    public const COMMAND_ENABLE = '/enable';
    public const COMMAND_DISABLE = '/disable';
    public const COMMAND_COMMAND = '/command';
    public const COMMAND_COMMANDS = '/commands';

    //Story related commands
    public const COMMAND_STORY = '/story';
    public const COMMAND_STORY_ADD = '/story_add';
    public const COMMAND_STORY_DEL = '/story_del';
    public const COMMAND_STORY_UP = '/story_up';
    public const COMMAND_STORY_DOWN = '/story_down';
    public const COMMAND_STORY_LAST = '/story_last';

    //User related commands
    public const COMMAND_TOP = '/top';
    public const COMMAND_ADMIN = '/admin';
    public const COMMAND_MODER = '/moder';

    //Common commands
    public const COMMAND_HELP = '/help';
    public const COMMAND_OBSCENE = '/obscene';
    public const COMMAND_FORTUNE = '/fortune';

    public const COMMANDS = [
        self::COMMAND_SAY => CommandSay::class,
        self::COMMAND_SORRY => CommandSorry::class,
        self::COMMAND_STATUS => CommandStatus::class,
        self::COMMAND_TOGGLE => CommandToggle::class,

        self::COMMAND_EVENTS => CommandEvents::class,
        self::COMMAND_ADD_EVENT => CommandAddEvent::class,
        self::COMMAND_DEL_EVENT => CommandDelEvent::class,
        self::COMMAND_SUBSCRIBE => CommandSubscribe::class,
        self::COMMAND_UNSUBSCRIBE => CommandUnsubscribe::class,

        self::COMMAND_ADD => CommandAdd::class,
        self::COMMAND_DEL => CommandDel::class,
        self::COMMAND_ENABLE => CommandEnable::class,
        self::COMMAND_DISABLE => CommandDisable::class,
        self::COMMAND_COMMAND => CommandReaction::class,
        self::COMMAND_COMMANDS => CommandReactions::class,

        self::COMMAND_STORY => CommandStory::class,
        self::COMMAND_STORY_ADD => CommandStoryAdd::class,
        self::COMMAND_STORY_DEL => CommandStoryDel::class,
        self::COMMAND_STORY_UP => CommandStoryVoteUp::class,
        self::COMMAND_STORY_DOWN => CommandStoryVoteDown::class,
        self::COMMAND_STORY_LAST => CommandStoryLast::class,

        self::COMMAND_TOP => CommandTop::class,
        self::COMMAND_ADMIN => CommandAdmin::class,
        self::COMMAND_MODER => CommandModer::class,

        self::COMMAND_OBSCENE => CommandObscene::class,
        self::COMMAND_FORTUNE => CommandFortune::class,
        self::COMMAND_HELP => CommandHelp::class,
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
        $commandWord = array_shift($messageTextParts);
        $payload = implode($delimiter, $messageTextParts);

        $isCommand = false;
        $commandClass = null;
        if (isset(self::COMMANDS[$commandWord])) {
            $commandClass = self::COMMANDS[$commandWord];
            $isCommand = true;
        } else {
            $reaction = Reaction::findOne($database, $messageText);
            if (null !== $reaction && $reaction->isEnabled()) {
                $isCommand = true;
                $payload = $reaction->getCommand();
                $commandClass = self::COMMANDS[self::COMMAND_COMMAND];
            } else {
                $isFortune = CommandFortune::check($messageText);
                $hasObscene = CommandObscene::check($messageText);
                if ($isFortune) {
                    $commandClass = self::COMMANDS[self::COMMAND_FORTUNE];
                } elseif ($hasObscene) {
                    $commandClass = self::COMMANDS[self::COMMAND_OBSCENE];
                }
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
        } catch (Throwable $throwable) {
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
