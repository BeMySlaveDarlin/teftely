<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

class User extends Model
{
    private int $id;
    private string $fromId;
    private int $isAdmin;

    private const STATUS_USER = 0;
    private const STATUS_MODER = 1;
    private const STATUS_ADMIN = 2;

    public function findOrCreate(string $fromId, ?array $userData = null): self
    {
        $userData = $userData ?? $this->database->db()
                ->select()
                ->from('users')
                ->where('from_id', '=', $fromId)
                ->run()
                ->fetch();

        if (false === $userData) {
            $table = $this->database->db()->table('users');
            $id = $table->insertOne([
                'from_id' => $fromId,
            ]);

            $this->id = $id;
            $this->fromId = $fromId;
            $this->isAdmin = 0;
        } else {
            $this->id = (int) $userData['id'];
            $this->fromId = (string) $userData['from_id'];
            $this->isAdmin = (int) $userData['is_admin'];
        }

        return $this;
    }

    public static function get(Database $database, string $fromId): self
    {
        $user = new self($database);

        return $user->findOrCreate($fromId);
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin === 2;
    }

    public function isModerator(): bool
    {
        return $this->isAdmin !== 0;
    }

    public function setAdmin(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('users')
                ->update(['is_admin' => 2])
                ->where('id', '=', $this->id)
                ->run();
        }

        return $this;
    }

    public function setModerator(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('users')
                ->update(['is_admin' => 1])
                ->where('id', '=', $this->id)
                ->run();
        }

        return $this;
    }
}
