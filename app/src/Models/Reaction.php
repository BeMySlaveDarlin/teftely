<?php

declare(strict_types = 1);

namespace Teftely\Models;

use RuntimeException;
use Teftely\Components\Database;

class Reaction extends Model
{
    private int $id;
    private int $status;
    private string $command;
    private ?string $message;
    private ?string $attachment;

    public const STATUS_ACTIVE = 1;
    public const STATUS_DISABLED = 0;

    public function assign($id, $status, string $command, ?string $message = null, ?string $attachment = null): self
    {
        $this->id = (int) $id;
        $this->status = (int) $status;
        $this->command = $command;
        $this->message = $message;
        $this->attachment = $attachment;

        return $this;
    }

    public static function findOrCreate(Database $database, ?string $command = null, ?array $reactionData = null): self
    {
        $user = self::findOne($database, $command);
        if (null === $user) {
            $user = self::createOne($database, $command, $reactionData);
        }

        return $user;
    }

    public static function findOne(Database $database, ?string $command = null): ?self
    {
        if (null === $command) {
            return null;
        }

        $result = $database->db()
            ->select()
            ->from('reactions')
            ->where('command', '=', $command)
            ->run()
            ->fetch();

        if (!empty($result)) {
            $reaction = new self($database);
            $reaction->assign(
                $result['id'],
                $result['status'],
                $result['command'],
                $result['message'],
                $result['attachment']
            );

            return $reaction;
        }

        return null;
    }

    public static function createOne(Database $database, ?string $command = null, ?array $reactionData = null): self
    {
        if (null === $reactionData && null === $command) {
            throw new RuntimeException('Cannot create reaction');
        }

        $table = $database->db()->table('reactions');
        $id = $table->insertOne([
            'command' => $command ?? $reactionData['command'],
            'status' => self::STATUS_ACTIVE,
            'message' => $reactionData['message'],
            'attachment' => $reactionData['attachment'],
        ]);

        $user = new self($database);
        $user->assign(
            $id,
            self::STATUS_ACTIVE,
            $reactionData['command'],
            $reactionData['message'],
            $reactionData['attachment']
        );

        return $user;
    }

    public static function findList(Database $database, $status = null): array
    {
        $reactions = [];
        $query = $database->db()->select()->from('reactions');
        if (null !== $status) {
            $query->where('status', '=', (int) $status);
        }
        $results = $query->fetchAll();

        if ($results) {
            foreach ($results as $result) {
                $reaction = new self($database);
                $reaction->assign(
                    $result['id'],
                    $result['status'],
                    $result['command'],
                    $result['message'],
                    $result['attachment']
                );
                $reactions[$result['id']] = $reaction;
            }
        }

        return $reactions;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getStatus(): string
    {
        return $this->status === self::STATUS_ACTIVE ? '&#9989;' : '&#128721;';
    }

    public function getMessage(?User $user = null): ?string
    {
        return null !== $user ? str_replace('{username}', $user->getName(), $this->message) : $this->message;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function hasAttachment(): ?string
    {
        return $this->attachment !== null ? 'Да' : 'Нет';
    }

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function delete(): bool
    {
        if (isset($this->id)) {
            $table = $this->database->db()->table('reactions');

            return (bool) $table->delete()
                ->where('command', '=', $this->command)
                ->run();
        }

        return false;
    }

    public function enable(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('reactions')
                ->update(['status' => self::STATUS_ACTIVE])
                ->where('id', '=', $this->id)
                ->run();
        }

        $this->status = self::STATUS_ACTIVE;

        return $this;
    }

    public function disable(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('reactions')
                ->update(['status' => self::STATUS_DISABLED])
                ->where('id', '=', $this->id)
                ->run();
        }

        $this->status = self::STATUS_DISABLED;

        return $this;
    }
}
