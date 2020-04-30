<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

class Peer extends Model
{
    private int $id;
    private string $peerId;
    private bool $isAdmin;
    private bool $isEnabled;

    public function findOrCreate($peerId, ?array $peerData = null): self
    {
        if (empty($peerData['id'])) {
            $peerData = $this->database->db()
                ->select()
                ->from('peers')
                ->where('peer_id', '=', (string) $peerId)
                ->run()
                ->fetch();
        }

        if (empty($peerData)) {
            $table = $this->database->db()->table('peers');
            $id = $table->insertOne([
                'peer_id' => $peerId,
            ]);

            $this->id = $id;
            $this->peerId = (string) $peerId;
            $this->isAdmin = false;
            $this->isEnabled = false;
        } else {
            $this->id = (int) $peerData['id'];
            $this->peerId = (string) $peerData['peer_id'];
            $this->isAdmin = (bool) ($peerData['is_admin'] ?? null);
            $this->isEnabled = (bool) $peerData['is_enabled'];
        }

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPeerId(): string
    {
        return $this->peerId;
    }

    public static function getOne(Database $database, $peerId): self
    {
        $peer = new self($database);

        return $peer->findOrCreate((string) $peerId);
    }

    public static function getList(Database $database): array
    {
        $peers = [];
        $query = $database->db()->select()->from('peers');
        $results = $query->fetchAll();

        if ($results) {
            foreach ($results as $result) {
                $peer = new self($database);
                $peer->findOrCreate(null, $result);
                $peers[$result['id']] = $peer;
            }
        }

        return $peers;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function enable(): bool
    {
        if (isset($this->id)) {
            return (bool) $this->database->db()->table('peers')
                ->update(['is_enabled' => 1])
                ->where('id', '=', $this->id)
                ->run();
        }

        return false;
    }

    public function disable(): bool
    {
        if (isset($this->id)) {
            return (bool) $this->database->db()->table('peers')
                ->update(['is_enabled' => 0])
                ->where('id', '=', $this->id)
                ->run();
        }

        return false;
    }
}
