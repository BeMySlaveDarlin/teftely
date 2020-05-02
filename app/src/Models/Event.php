<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

class Event extends Model
{
    private int $id;
    private string $code;
    private string $name;
    private string $message;
    private ?string $time;
    private ?string $week;
    private ?string $attachment;
    private ?string $peerId;

    public function assign(
        $id,
        string $name,
        string $message,
        ?string $time = null,
        ?string $week = null,
        ?string $attachment = null,
        ?string $peerId = null
    ): self {
        $this->id = (int) $id;
        $this->name = $name;
        $this->message = $message;
        $this->time = $time;
        $this->week = $week;
        $this->attachment = $attachment;
        $this->peerId = $peerId;

        return $this;
    }

    public static function findOrCreate(Database $database, ?int $eventId = null, ?array $eventData = []): self
    {
        $event = self::findOne($database, $eventId ?? $eventData['id']);
        if (null === $event) {
            $event = self::createOne($database, $eventData);
        }

        return $event;
    }

    public static function findOne(Database $database, ?int $eventId = null): ?self
    {
        if (null === $eventId) {
            return null;
        }

        $result = $database->db()
            ->select()
            ->from('events')
            ->where('id', '=', $eventId)
            ->run()
            ->fetch();

        if (!empty($result)) {
            $event = new self($database);
            $event->assign(
                $result['id'],
                $result['name'],
                $result['message'],
                $result['time'],
                $result['week'],
                $result['attachment']
            );

            return $event;
        }

        return null;
    }

    public static function createOne(Database $database, array $eventData): ?self
    {
        if (empty($eventData['message'])) {
            return null;
        }

        $table = $database->db()->table('events');
        if (!empty($eventData['time'])) {
            $eventData['time'] = date('H:i:00', strtotime('2020-01-01 ' . $eventData['time']));
        }
        $eventData = [
            'name' => $eventData['name'] ?? (string) time(),
            'message' => $eventData['message'],
            'time' => $eventData['time'] ?? null,
            'week' => $eventData['week'] ?? null,
            'attachment' => $eventData['attachment'] ?? null,
        ];
        $id = $table->insertOne($eventData);

        $event = new self($database);
        $event->assign(
            $id,
            $eventData['name'],
            $eventData['message'],
            $eventData['time'],
            $eventData['week'],
            $eventData['attachment'],
            $eventData['peer_id']
        );

        return $event;
    }

    public static function findList(Database $database, ?string $time = null): array
    {
        $events = [];
        $query = $database->db()->select()->from('events');
        if (null !== $time) {
            $query->where('time', '=', $time);
        }
        $results = $query->fetchAll();

        if ($results) {
            foreach ($results as $result) {
                $event = new self($database);
                $event->assign(
                    $result['id'],
                    $result['name'],
                    $result['message'],
                    $result['time'],
                    $result['week'],
                    $result['attachment']
                );
                $events[$result['id']] = $event;
            }
        }

        return $events;
    }

    public static function findListActive(Database $database, ?string $time = null, $peerId = null): array
    {
        $query = $database->db()
            ->select(['events.*', 'peers_events.peer_id'])
            ->from('events')
            ->innerJoin('peers_events')
            ->on(['peers_events.event_id' => 'events.id'])
            ->innerJoin('peers')
            ->on(['peers.peer_id' => 'peers_events.peer_id'])
            ->where('peers.is_enabled', '=', 1);
        if (null !== $time) {
            $query->where('events.time', '=', $time);
        }
        $query->where('peers_events.peer_id', '=', $peerId)
            ->groupBy('peers_events.peer_id');

        $peersEvents = [];
        $results = $query->groupBy('events.id')->fetchAll();
        if ($results) {
            foreach ($results as $result) {
                $event = new self($database);
                $event->assign(
                    $result['id'],
                    $result['name'],
                    $result['message'],
                    $result['time'],
                    $result['week'],
                    $result['attachment'],
                    $result['peer_id']
                );
                $peersEvents[$result['id']] = $event;
            }
        }

        return $peersEvents;
    }

    public function isRandom(): bool
    {
        return null === $this->time;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTime(): string
    {
        return null !== $this->time ? date('d.m.Y ') . substr($this->time, 0, -3) : '--';
    }

    public function getWeek(): ?string
    {
        return $this->week;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return trim($this->message);
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function getPeerId(): ?string
    {
        return $this->peerId;
    }

    public function getFormattedMessage($peersEvents): string
    {
        $statusId = isset($peersEvents[$this->getId()]) ? '&#9989;' : '&#128721;';

        $message = "Событие: #{$this->getId()} | {$this->getName()}\n";
        $message .= "Описание: {$this->getMessage()}\n";
        $message .= "Время: {$this->getTime()}\n";
        $message .= "Статус: {$statusId}\n\n";

        return $message;
    }

    public function delete(): bool
    {
        if (isset($this->id)) {
            $table = $this->database->db()->table('events');

            return (bool) $table->delete()
                ->where('id', '=', $this->id)
                ->run();
        }

        return false;
    }

    public function enable($peerId): bool
    {
        if (isset($this->id)) {
            $table = $this->database->db()->table('peers_events');

            return (bool) $table->insertOne([
                'peer_id' => $peerId,
                'event_id' => $this->id,
            ]);
        }

        return false;
    }

    public function disable($peerId): bool
    {
        if (isset($this->id)) {
            $table = $this->database->db()->table('peers_events');

            return (bool) $table->delete()
                ->where('peer_id', '=', $peerId)
                ->where('event_id', '=', $this->id)
                ->run();
        }

        return false;
    }
}
