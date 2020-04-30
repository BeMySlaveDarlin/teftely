<?php

declare(strict_types = 1);

use Teftely\Components\Application;
use Teftely\Components\Config;
use Teftely\Components\Database;

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require BASE_PATH . '/config/bootstrap.php';

$config = require CNF_PATH . 'config.php';
$database = new Database($config->get(Config::DB_CONFIG));

$application = new Application($config, $database);
$response = $application->handle();

echo $response;

