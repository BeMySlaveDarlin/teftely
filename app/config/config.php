<?php

declare(strict_types = 1);

use Teftely\Components\Config;

return new Config([
    'db' => [
        'host' => getenv('MYSQL_HOST'),
        'port' => getenv('MYSQL_PORT'),
        'database'=> getenv('MYSQL_DATABASE'),
        'user'=> getenv('MYSQL_USER'),
        'password'=> getenv('MYSQL_PASSWORD'),
    ],
]);
