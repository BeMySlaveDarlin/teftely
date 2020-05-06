<?php

declare(strict_types = 1);

namespace Teftely\Models\Story;

use Teftely\Components\Database;
use Teftely\Models\Model;
use Teftely\Models\User;

class Chapter extends Model
{
    private int $id;
    private string $fromId;
    private string $chapter;
    private ?string $photo;
    private int $status = self::STATUS_PENDING;

    public const STATUS_PUBLISHED = 1;
    public const STATUS_PENDING = 0;

    public function assign(
        $id,
        string $fromId,
        string $chapter,
        ?string $photo = null,
        $status = self::STATUS_PENDING
    ): self {
        $this->id = (int) $id;
        $this->fromId = $fromId;
        $this->chapter = $chapter;
        $this->status = (int) $status;
        $this->photo = $photo;

        return $this;
    }

    public static function findOrCreate(Database $database, ?string $chapterId = null, ?array $chapterData = []): self
    {
        $chapter = self::findOne($database, $chapterId ?? $chapterData['id']);
        if (null === $chapter) {
            $chapter = self::createOne($database, $chapterData);
        }

        return $chapter;
    }

    public static function findOne(Database $database, ?string $chapterId = null): ?self
    {
        if (null === $chapterId) {
            return null;
        }

        $result = $database->db()
            ->select()
            ->from('story_chapters')
            ->where('id', '=', $chapterId)
            ->run()
            ->fetch();

        if (!empty($result)) {
            $chapter = new self($database);
            $chapter->assign(
                $result['id'],
                $result['from_id'],
                $result['chapter'],
                $result['photo'],
                $result['status']
            );

            return $chapter;
        }

        return null;
    }

    public static function createOne(Database $database, array $chapterData): ?self
    {
        if (empty($chapterData['chapter']) || empty($chapterData['from_id'])) {
            return null;
        }

        $table = $database->db()->table('story_chapters');
        $id = $table->insertOne($chapterData);

        $chapter = new self($database);
        $chapter->assign(
            $id,
            $chapterData['from_id'],
            $chapterData['chapter'],
            $chapterData['photo']
        );

        return $chapter;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getAuthor(): User
    {
        return User::findOne($this->database, $this->fromId);
    }

    public function getChapter(): string
    {
        return $this->chapter;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function getVotes(): ?array
    {
        return null;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function publish(): self
    {
        if (isset($this->id)) {
            $this->database->db()->table('story_chapters')
                ->update(['status' => self::STATUS_PUBLISHED])
                ->where('id', '=', $this->id)
                ->run();
        }

        $this->status = self::STATUS_PUBLISHED;

        return $this;
    }

    public function delete(string $fromId): bool
    {
        $user = User::findOne($this->database, $fromId);
        if (isset($this->id)) {
            if (($fromId === $this->fromId && $this->isPending()) || $user->isAdmin()) {
                $table = $this->database->db()->table('story_chapters');

                return (bool) $table->delete()
                    ->where('id', '=', $this->id)
                    ->run();
            }
        }

        return false;
    }
}
