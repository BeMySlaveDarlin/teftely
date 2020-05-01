<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Payload
{
    protected object $request;
    protected ?string $payload = null;

    public function __construct(object $request, ?string $payload = null)
    {
        $this->request = $request;
        $this->payload = $payload;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function getPeerId(): int
    {
        return $this->request->object->message->peer_id ?? $this->request->object->message->user_id;
    }

    public function getFromId(): int
    {
        return $this->request->object->message->from_id;
    }

    public function getMessage(): object
    {
        return $this->request->object->message;
    }

    public function getAttachment(): ?string
    {
        $attachments = $this->request->object->message->attachments ?? null;
        if (is_array($attachments) || !empty($attachments[0])) {
            $attachment = $attachments[0];
            $type = $attachment->type;
            $id = $attachment->$type->id ?? null;
            $ownerId = $attachment->$type->owner_id ?? null;
            $accessKey = $attachment->$type->access_key ?? null;
            if (null !== $id) {
                if (null !== $accessKey) {
                    return "{$type}{$ownerId}_{$id}_($accessKey)";
                }

                return "{$type}{$ownerId}_{$id}";
            }
        }

        return null;
    }

    public function getSecret(): string
    {
        return $this->request->secret;
    }

    public function getGroupId(): int
    {
        return $this->request->group_id;
    }

    public function getEventId(): ?int
    {
        return $this->request->event_id;
    }
}
