<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

class User extends Model
{
    private int $id;
    private string $fromId;
    private int $isAdmin = 0;
    private ?string $fullName;

    private const STATUS_USER = 0;
    private const STATUS_MODER = 1;
    private const STATUS_ADMIN = 2;

    public function assign($id, string $fromId, ?string $fullName = null, $isAdmin = 0): self
    {
        $this->id = (int) $id;
        $this->fromId = $fromId;
        $this->fullName = $fullName;
        $this->isAdmin = (int) $isAdmin;

        return $this;
    }

    public static function findOrCreate(Database $database, string $fromId): self
    {
        $user = self::findOne($database, $fromId);
        if (null === $user) {
            $user = self::createOne($database, $fromId, null);
        }

        return $user;
    }

    public static function findOne(Database $database, ?string $fromId = null): ?self
    {
        if (null === $fromId) {
            return null;
        }

        $result = $database->db()
            ->select()
            ->from('users')
            ->where('from_id', '=', $fromId)
            ->run()
            ->fetch();

        if (!empty($result)) {
            $user = new self($database);
            $user->assign($result['id'], $result['from_id'], $result['full_name'], $result['is_admin']);

            return $user;
        }

        return null;
    }

    public static function createOne(Database $database, string $fromId, ?string $fullName = null): self
    {
        $table = $database->db()->table('users');
        $id = $table->insertOne([
            'from_id' => $fromId,
            'full_name' => $fullName ?? null,
        ]);

        $user = new self($database);
        $user->assign($id, $fromId, $fullName);

        return $user;
    }

    public static function topTalkers(Database $database, $peerId = null)
    {
        $query = $database->db()
            ->select(['users.full_name', 'COUNT(messages.id) as msg'])
            ->from('users')
            ->innerJoin('messages')
            ->on(['messages.from_id' => 'users.from_id'])
            ->limit(5)
            ->orderBy('msg', 'DESC');
        if (null !== $peerId) {
            $query->where('messages.peer_id', '=', $peerId);
        }

        return $query->groupBy('users.from_id')->fetchAll();
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getName(): ?string
    {
        return explode(' ', (string) $this->fullName)[0] ?? 'Эй ты';
    }

    public function getFromId(): ?string
    {
        return $this->fromId;
    }

    public function isUser(): bool
    {
        return $this->isAdmin === self::STATUS_USER;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin === self::STATUS_ADMIN;
    }

    public function isModerator(): bool
    {
        return $this->isAdmin >= self::STATUS_MODER;
    }

    public function setAdmin(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('users')
                ->update(['is_admin' => self::STATUS_ADMIN])
                ->where('id', '=', $this->id)
                ->run();
        }

        $this->isAdmin = self::STATUS_ADMIN;

        return $this;
    }

    public function setModerator(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('users')
                ->update(['is_admin' => self::STATUS_MODER])
                ->where('id', '=', $this->id)
                ->run();
        }

        $this->isAdmin = self::STATUS_MODER;

        return $this;
    }

    public function setUser(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('users')
                ->update(['is_admin' => self::STATUS_USER])
                ->where('id', '=', $this->id)
                ->run();
        }

        $this->isAdmin = self::STATUS_USER;

        return $this;
    }
}
