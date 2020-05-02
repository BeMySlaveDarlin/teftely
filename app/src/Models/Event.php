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
    private ?string $attachment;
    private ?string $peerId;

    public function findOrCreate(?int $eventId = null, ?array $eventData = []): self
    {
        if (!empty($eventData['message']) && empty($eventData['id'])) {
            $table = $this->database->db()->table('events');
            if (!empty($eventData['time'])) {
                $eventData['time'] = date('H:i:00', strtotime('2020-01-01 ' . $eventData['time']));
            }
            $eventData = [
                'name' => $eventData['name'] ?? (string) time(),
                'message' => $eventData['message'],
                'time' => $eventData['time'] ?? null,
                'attachment' => $eventData['attachment'] ?? null,
            ];
            $id = $table->insertOne($eventData);

            $this->id = (int) $id;
            $this->name = $eventData['name'];
            $this->message = $eventData['message'];
            $this->time = $eventData['time'];
            $this->attachment = null;
            $this->peerId = null;
        } else {
            if ($eventId) {
                $eventData = $this->database->db()
                    ->select()
                    ->from('events')
                    ->where('id', '=', $eventId)
                    ->run()
                    ->fetch();
            }

            if ($eventData) {
                $this->id = (int) $eventData['id'];
                $this->name = $eventData['name'];
                $this->message = $eventData['message'];
                $this->time = $eventData['time'];
                $this->attachment = $eventData['attachment'] ?? null;
                $this->peerId = $eventData['peer_id'] ?? null;
            }
        }
        if (!isset($this->id) || null === $this->id) {
            throw new \RuntimeException('No data provided');
        }

        return $this;
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

    public static function getOne(Database $database, $eventId): self
    {
        $event = new self($database);

        return $event->findOrCreate((int) $eventId);
    }

    public static function getList(Database $database, ?string $time = null): array
    {
        $events = [];
        $query = $database->db()->select(['events.*'])->from('events');
        if (null !== $time) {
            $query->where('time', '=', $time);
        }
        $results = $query->fetchAll();

        if ($results) {
            foreach ($results as $result) {
                $event = new self($database);
                $event->findOrCreate(null, $result);
                $events[$result['id']] = $event;
            }
        }

        return $events;
    }

    public static function getListActive(Database $database, ?string $time = null, $peerId = null): array
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
        if (null !== $peerId) {
            $query->where('peers_events.peer_id', '=', $peerId)
                ->groupBy('peers_events.peer_id');
        }

        $peersEvents = [];
        $results = $query->groupBy('events.id')->fetchAll();
        if ($results) {
            foreach ($results as $result) {
                $event = new self($database);
                $event->findOrCreate(null, $result);
                $peersEvents[$result['id']] = $event;
            }
        }

        return $peersEvents;
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
