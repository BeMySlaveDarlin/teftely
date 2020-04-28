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

    public function findOrCreate(?int $eventId = null, ?array $eventData = []): self
    {
        if (!empty($eventData['message']) && null === $eventId) {
            $table = $this->database->db()->table('events');
            if (!empty($eventData['time'])) {
                $eventData['time'] = date('H:i:00', strtotime('2020-01-01 ' . $eventData['time']));
            }
            $eventData = [
                'name' => $eventData['name'] ?? (string) time(),
                'message' => $eventData['message'] ?? null,
                'time' => $eventData['time'] ?? null,
            ];
            $id = $table->insertOne($eventData);

            $this->id = (int) $id;
            $this->name = $eventData['name'];
            $this->message = $eventData['message'];
            $this->time = $eventData['time'];
        } elseif (null !== $eventId) {
            if (empty($eventData['id'])) {
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
        return null !== $this->time ? date('d.m.Y ') . substr($this->time, 0, -3) : 'Случайно';
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public static function getList(Database $database, ?string $time = null, ?int $peerId = null): array
    {
        $events = [];
        $query = $database->db()->select(['events.*'])->from('events');
        if (null !== $peerId) {
            $query->innerJoin('peers_events')
                ->on(['peers_events.event_id' => 'events.id'])
                ->onWhere('peers_events.peer_id', $peerId);
        }
        if (null !== $time) {
            $query->where('time', '=', $time);
        }
        $results = $query->fetchAll();

        if ($results) {
            foreach ($results as $result) {
                $event = new self($database);
                $event->findOrCreate((int) $result['id'], $result);
                $events[(int) $result['id']] = $event;
            }
        }

        return $events;
    }

    public static function getActive(Database $database, int $peerId): array
    {
        $results = $database->db()
            ->select('event_id')
            ->from('peers_events')
            ->where('peer_id', '=', $peerId)
            ->fetchAll();

        $peersEvents = [];
        if ($results) {
            foreach ($results as $result) {
                $peersEvents[] = (int) $result['event_id'];
            }
        }

        return $peersEvents;
    }

    public function enable(int $peerId): bool
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

    public function disable(int $peerId): bool
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
}
