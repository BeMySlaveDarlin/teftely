<?php

declare(strict_types = 1);

namespace Teftely\Messages;

interface MessageInterface
{
    public function toArray(): array;
}
