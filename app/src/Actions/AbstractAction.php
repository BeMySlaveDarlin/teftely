<?php

declare(strict_types = 1);

namespace Teftely\Actions;

use Teftely\Components\Config;
use Teftely\Components\Request;
use Teftely\Components\Response;

abstract class AbstractAction
{
    protected Config $config;
    protected Request $request;
    protected Response $response;

    public function __construct(Config $config, Request $request, Response $response)
    {
        $this->config = $config;
        $this->request = $request;
        $this->response = $response;
    }

    abstract public function run(): void;
}
