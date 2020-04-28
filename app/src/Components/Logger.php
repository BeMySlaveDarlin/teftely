<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Monolog\Handler\StreamHandler;

class Logger extends \Monolog\Logger
{
    public function __construct()
    {
        parent::__construct('teftely');
        $this->pushHandler(new StreamHandler(LOGS_PATH, self::INFO));
    }
}
