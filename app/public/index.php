<?php

declare(strict_types = 1);

use Teftely\Components\Application;

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('SERVER_NAME', $_SERVER['SERVER_NAME'] ?? php_uname('n'));
define('APP_PATH', BASE_PATH . 'src' . DS);
define('CNF_PATH', BASE_PATH . 'config' . DS);
define('VENDOR_PATH', BASE_PATH . 'vendor' . DS);

include VENDOR_PATH . 'autoload.php';
include CNF_PATH . 'envs.php';

$application = new Application(CNF_PATH . 'config.php');
$application->handle();

