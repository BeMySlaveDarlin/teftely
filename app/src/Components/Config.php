<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Config
{
    public const VK_CONFIG = 'vk';
    public const DB_CONFIG = 'db';
    public const SECRET = 'secret';

    public array $configs = [];

    public function __construct(array $params = [])
    {
        foreach ($params as $key => $values) {
            if (is_array($values)) {
                $this->configs[$key] = new self($values);
            } else {
                $this->configs[$key] = $values;
            }
        }
    }

    public function get(string $key)
    {
        return $this->configs[$key] ?? null;
    }
}
