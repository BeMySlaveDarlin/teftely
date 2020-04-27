<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Teftely\Messages\MessageInterface;

class Response
{
    private ?MessageInterface $content = null;

    public function __construct(?MessageInterface $content = null)
    {
        $this->content = $content;
    }

    public function setContent(?MessageInterface $content): void
    {
        $this->content = $content;
    }

    public function send(): void
    {
        if ($this->content instanceof MessageInterface) {
            echo json_encode(
                $this->content->toArray(),
                JSON_THROW_ON_ERROR
            );
        } else {
            echo 'OK';
        }
    }
}
