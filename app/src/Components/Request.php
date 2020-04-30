<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Request
{
    public array $request;
    public array $server;

    public function __construct()
    {
        $this->request = $_REQUEST;
        $this->server = $_SERVER;
    }

    public function getData(): ?object
    {
        $input = file_get_contents('php://input');
        if (false === $input || (false === strpos($input, '{') && false === strpos($input, '}'))) {
            return null;
        }

        return json_decode($input, false, 512, JSON_THROW_ON_ERROR);
    }

    public function get(?string $key = null)
    {
        return $this->request[$key] ?? $this->request;
    }

    public function srv(?string $key = null)
    {
        return $this->server[$key] ?? $this->server;
    }
}
