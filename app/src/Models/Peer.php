<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

class Peer extends Model
{
    private int $id;
    private string $peerId;
    private bool $isEnabled;

    public function assign(
        $id,
        $peerId,
        $isEnabled = null
    ): self {
        $this->id = (int) $id;
        $this->peerId = (string) $peerId;
        $this->isEnabled = (bool) $isEnabled;

        return $this;
    }

    public static function findOrCreate(Database $database, $peerId): self
    {
        $peer = self::findOne($database, $peerId);
        if (null === $peer) {
            $peer = self::createOne($database, $peerId);
        }

        return $peer;
    }

    public static function findOne(Database $database, $peerId): ?self
    {
        if (null === $peerId) {
            return null;
        }

        $result = $database->db()
            ->select()
            ->from('peers')
            ->where('peer_id', '=', $peerId)
            ->run()
            ->fetch();

        if (!empty($result)) {
            $peer = new self($database);
            $peer->assign(
                $result['id'],
                $result['peer_id'],
                $result['is_enabled']
            );

            return $peer;
        }

        return null;
    }

    public static function createOne(Database $database, $peerId): ?self
    {
        $table = $database->db()->table('peers');
        $peerData = [
            'peer_id' => $peerId,
        ];
        $id = $table->insertOne($peerData);

        $peer = new self($database);
        $peer->assign(
            $id,
            $peerId
        );

        return $peer;
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

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPeerId(): string
    {
        return $this->peerId;
    }
}
