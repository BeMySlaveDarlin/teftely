<?php

declare(strict_types = 1);

$dotEnv = Dotenv\Dotenv::create(BASE_PATH);
$dotEnv->overload();
