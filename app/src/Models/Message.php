<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

class Message extends Model
{
    private int $id;
    private string $peerId;
    private string $fromId;
    private string $message;
    private string $time;

    public static function create(Database $database, $peerId, $fromId, string $text): self
    {
        $table = $database->db()->table('messages');
        $data = [
            'peer_id' => (string) $peerId,
            'from_id' => (string) $fromId,
            'message' => htmlentities($text),
            'time' => date('Y-m-d H:i:s'),
        ];
        $id = $table->insertOne($data);
        $data['id'] = $id;

        $message = new self($database);

        return $message->assign($data);
    }

    public static function findRandom(Database $database): string
    {
        $query = $database->db()
            ->select(['message'])
            ->from('messages')
            ->orderBy('RAND()')
            ->limit(1);

        return $query->run()->fetchColumn();
    }

    public static function total(Database $database, $peerId = null)
    {
        $query = $database->db()
            ->select()
            ->from('messages');
        if (null !== $peerId) {
            $query->where('peer_id', '=', $peerId);
        }

        return $query->count();
    }

    public function assign(array $data): self
    {
        $this->id = $data['id'];
        $this->peerId = $data['peer_id'];
        $this->fromId = $data['from_id'];
        $this->message = $data['message'];
        $this->time = $data['time'];

        return $this;
    }
}
