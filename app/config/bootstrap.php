<?php

declare(strict_types = 1);

define('SERVER_NAME', $_SERVER['SERVER_NAME'] ?? php_uname('n'));
define('APP_PATH', BASE_PATH . 'src' . DS);
define('CNF_PATH', BASE_PATH . 'config' . DS);
define('VENDOR_PATH', BASE_PATH . 'vendor' . DS);
define('LOGS_PATH', BASE_PATH . 'logs' . DS . 'events.log');

include VENDOR_PATH . 'autoload.php';
include CNF_PATH . 'envs.php';
