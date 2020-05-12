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
    private int $status = self::STATUS_PENDING;

    public const STATUS_PUBLISHED = 1;
    public const STATUS_PENDING = 0;

    public function assign(
        $id,
        string $fromId,
        string $chapter,
        $status = self::STATUS_PENDING
    ): self {
        $this->id = (int) $id;
        $this->fromId = $fromId;
        $this->chapter = $chapter;
        $this->status = (int) $status;

        return $this;
    }

    public static function findOne(Database $database, string $chapterId): ?self
    {
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
                $result['status']
            );

            return $chapter;
        }

        return null;
    }

    public static function findLast(Database $database): ?self
    {
        $result = $database->db()
            ->select()
            ->from('story_chapters')
            ->orderBy('id', 'DESC')
            ->run()
            ->fetch();

        if (!empty($result)) {
            $chapter = new self($database);
            $chapter->assign(
                $result['id'],
                $result['from_id'],
                $result['chapter'],
                $result['status']
            );

            return $chapter;
        }

        return null;
    }

    public static function findList(Database $database): array
    {
        $chapters = [];
        $results = $database->db()
            ->select()
            ->from('story_chapters')
            ->where('status', '=', 1)
            ->fetchAll();

        if ($results) {
            foreach ($results as $result) {
                $chapter = new self($database);
                $chapter->assign(
                    $result['id'],
                    $result['from_id'],
                    $result['chapter'],
                    $result['status']
                );
                $chapters[] = $chapter;
            }
        }

        return $chapters;
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
            $chapterData['chapter']
        );

        return $chapter;
    }

    public function getVotes(): string
    {
        $query = $this->database->db()
            ->select(['SUM(vote)'])
            ->from('story_votes')
            ->where('chapter_id', '=', $this->id)
            ->groupBy('chapter_id');

        return $query->run()->fetchColumn() ?: '0';
    }

    public function isVoted(string $fromId): ?string
    {
        $query = $this->database->db()
            ->select(['id'])
            ->from('story_votes')
            ->where('chapter_id', '=', $this->id)
            ->where('from_id', '=', $fromId)
            ->limit(1)
            ->run();

        return $query->fetchColumn() ?: null;
    }

    public function vote(string $fromId, bool $vote = true): bool
    {
        $table = $this->database->db()->table('story_votes');

        return (bool) $table->insertOne([
            'from_id' => $fromId,
            'chapter_id' => $this->id,
            'vote' => $vote ? 1 : -1,
        ]);
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getFromId(): ?string
    {
        return $this->fromId;
    }

    public function getAuthor(): string
    {
        $user = User::findOne($this->database, $this->fromId);
        if (null !== $user) {
            return $user->getFullName();
        }

        return 'Неизвестный';
    }

    public function getChapter(): string
    {
        return $this->chapter;
    }

    public function getChapterHtml(): string
    {
        return str_replace("\n", '<br>', $this->chapter);
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
        if (isset($this->id) && null !== $user) {
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
