<?php

declare(strict_types = 1);

use Teftely\Components\Config;

return new Config([
    Config::VK_CONFIG => [
        'password' => getenv('ADMIN_PASSWORD'),
        'secret' => getenv('SECRET_CODE'),
        'confirmation' => getenv('CONFIRMATION_CODE'),
        'access_token' => getenv('ACCESS_TOKEN'),
        'api_version' => getenv('API_VERSION'),
        'api_endpoint' => getenv('API_ENDPOINT'),
        'group_id' => getenv('GROUP_ID'),
    ],
    Config::DB_CONFIG => [
        'host' => getenv('MYSQL_HOST'),
        'db_name'=> getenv('MYSQL_DATABASE'),
        'user'=> getenv('MYSQL_USER'),
        'password'=> getenv('MYSQL_PASSWORD'),
    ],
]);
