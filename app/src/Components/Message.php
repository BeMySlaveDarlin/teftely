<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Models\Event;
use Teftely\Models\Peer;
use Teftely\Models\User;

class Message implements \JsonSerializable
{
    public const METHOD_SEND = 'messages.send';

    public const COMMAND_HELP = '/help';
    public const COMMAND_ADMIN = '/admin';
    public const COMMAND_TOGGLE = '/toggle';
    public const COMMAND_STATUS = '/status';
    public const COMMAND_SUBSCRIBE = '/subscribe';
    public const COMMAND_UNSUBSCRIBE = '/unsubscribe';
    public const COMMAND_EVENTS = '/list';
    public const COMMAND_ADD_EVENT = '/add_event';
    public const COMMAND_DEL_EVENT = '/del_event';

    public const AVAILABLE_COMMANDS = [
        self::COMMAND_HELP => 'доступные команды',
        self::COMMAND_EVENTS => 'список событий',
        self::COMMAND_STATUS => 'статус бота',
        self::COMMAND_TOGGLE => 'включить/отключить бота',
        self::COMMAND_SUBSCRIBE => 'подписаться на событие <ID>',
        self::COMMAND_UNSUBSCRIBE => 'отписаться от события <ID>',
        self::COMMAND_ADD_EVENT => 'новое событие HH:mm;Название;Описание. Пример: /add_event 08:00;Парам-пам;Пора вставать и унывать',
        self::COMMAND_DEL_EVENT => 'удалить событие <ID>',
    ];

    private Database $database;
    private Config $config;

    private Peer $peer;
    private User $user;
    private array $events;
    private array $peersEvents;

    private string $method = self::METHOD_SEND;
    private object $message;
    private array $response = [];

    public function __construct(object $message, Config $config, Database $database)
    {
        $this->config = $config;
        $this->database = $database;
        $this->message = $message;
        $this->response['peer_id'] = $message->peer_id ?: $message->user_id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function resolve(): bool
    {
        $text = $this->message->text ?: null;
        $textParts = is_string($text) ? explode(' ', $text) : [];
        $command = array_shift($textParts);
        $payload = implode(' ', $textParts);

        switch ($command) {
            default:
                return false;
            case self::COMMAND_HELP:
                return $this->help();
            case self::COMMAND_TOGGLE:
                return $this->toggle();
            case self::COMMAND_STATUS:
                return $this->status();
            case self::COMMAND_EVENTS:
                return $this->events();
            case self::COMMAND_SUBSCRIBE:
                return $this->subscribe((int) $payload, true);
            case self::COMMAND_UNSUBSCRIBE:
                return $this->subscribe((int) $payload, false);
            case self::COMMAND_ADD_EVENT:
                return $this->addEvent((string) $payload);
            case self::COMMAND_DEL_EVENT:
                return $this->delEvent((int) $payload);
            case self::COMMAND_ADMIN:
                return $this->admin($payload);
        }
    }

    private function initDb(): void
    {
        $this->user = User::get($this->database, (string) $this->message->from_id);
        $this->peer = Peer::getPeer($this->database, $this->response['peer_id']);
        $this->events = Event::getList($this->database);
        $this->peersEvents = Event::getActive($this->database, $this->response['peer_id']);
    }

    private function help(): bool
    {
        $message = "Доступные команды:\n\n";
        foreach (self::AVAILABLE_COMMANDS as $command => $description) {
            $message .= $command . ' - ' . $description . "\n";
        }

        $this->response['message'] = $message;

        return true;
    }

    private function events(): bool
    {
        $this->initDb();

        $message = ["Список событий:\n\n"];
        if (false === $this->peer->isEnabled()) {
            $command = self::COMMAND_TOGGLE;
            $message[] = "Бот отключен! События игнорируются! [$command] чтобы включить\n\n";
        }

        foreach ($this->events as $eventId => $event) {
            $message[] = "ID: {$eventId}\n";
            $message[] = "Событие: {$event->getName()}\n";
            $message[] = "Время: {$event->getTime()}\n";
            $message[] = "Описание: {$event->getMessage()}\n";
            if (in_array($eventId, $this->peersEvents, false)) {
                $command = self::COMMAND_UNSUBSCRIBE . ' ' . $eventId;
                $message[] = "Статус: Активное. [$command] чтобы отключить \n\n";
            } else {
                $command = self::COMMAND_SUBSCRIBE . ' ' . $eventId;
                $message[] = "Статус: Отключено. [$command] чтобы включить \n\n";
            }
        }

        $this->response['message'] = implode('', $message);

        return true;
    }

    private function subscribe(int $eventId, bool $status): bool
    {
        $this->initDb();

        if ($this->user->isAdmin()) {
            $event = $this->events[$eventId];
            $peerId = $this->peer->getPeerId();
            if ($status) {
                if (!in_array($eventId, $this->peersEvents, false)) {
                    $event->enable($peerId);
                }
                $message = "Событие #$eventId включено";
            } else {
                if (in_array($eventId, $this->peersEvents, false)) {
                    $event->disable($peerId);
                }
                $message = "Событие #$eventId отключено";
            }
        } else {
            $message = 'Только админ бота может переключать события';
        }

        $this->response['message'] = $message;

        return true;
    }

    private function addEvent(string $payload): bool
    {
        $this->initDb();

        if ($this->user->isAdmin()) {
            $message = 'Некорректный формат добавления события';
            $message .= 'Ожидается [/add_event HH:mm;Название;Описание]';
            if (false !== strpos($payload, ';')) {
                [$time, $name, $text] = explode(';', $payload);
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

                    $event = new Event($this->database);
                    $event->findOrCreate(null, [
                        'time' => $time,
                        'name' => $name,
                        'message' => $text,
                    ]);

                    $id = $event->getId();
                    $message = $id ? "Создано событие #$id" : 'Не удалось создать';
                }
            }
        } else {
            $message = 'Только админ бота может удалять события';
        }

        $this->response['message'] = $message;

        return true;
    }

    private function delEvent(int $eventId): bool
    {
        $this->initDb();

        if ($this->user->isAdmin()) {
            $event = $this->events[$eventId];
            $message = $event->delete()
                ? "Событие #{$event->getId()} удалено"
                : "Событие #{$event->getId()} не удалено";
        } else {
            $message = 'Только админ бота может удалять события';
        }

        $this->response['message'] = $message;

        return true;
    }

    private function admin(string $password): bool
    {
        $this->initDb();

        if ($password === $this->config->get('password')) {
            $this->user->setAdmin();
            $message = 'Вы получили права администратора';
        } else {
            $message = 'Для получения прав администратора требуется пароль';
        }

        $this->response['message'] = $message;

        return true;
    }

    private function toggle(): bool
    {
        $this->initDb();

        if ($this->user->isAdmin()) {
            if ($this->peer->isEnabled()) {
                $message = $this->peer->disable() ? 'Бот отключен' : 'Не удалось отключить';
            } else {
                $message = $this->peer->enable() ? 'Бот включен' : 'Не удалось включить';
            }
        } else {
            $message = 'Только администратор бота может переключить его';
        }

        $this->response['message'] = $message;

        return true;
    }

    private function status(): bool
    {
        $this->initDb();

        $message = $this->peer->isEnabled() ? 'Бот включен' : 'Бот отключен';

        $this->response['message'] = $message;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
