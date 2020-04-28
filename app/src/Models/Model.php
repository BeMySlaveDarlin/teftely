<?php

declare(strict_types = 1);

namespace Teftely\Models;

use Teftely\Components\Database;

abstract class Model
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}
