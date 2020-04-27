<?php

declare(strict_types = 1);

namespace Teftely\Messages;

class Message implements MessageInterface
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
