<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Request
{
    public $request;
    public $server;

    public function __construct()
    {
        $this->request = $_REQUEST;
        $this->server = $_SERVER;
    }

    public function getRequest(?string $key = null)
    {
        return $this->request[$key] ?? $this->request;
    }

    public function getServer(?string $key = null)
    {
        return $this->server[$key] ?? $this->server;
    }
}
