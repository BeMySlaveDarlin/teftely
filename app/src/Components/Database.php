<?php

declare(strict_types = 1);

namespace Teftely\Components;

use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\DatabaseManager;
use Spiral\Database\Driver\MySQL\MySQLDriver;

class Database
{
    private DatabaseManager $dbm;

    public function __construct(Config $config)
    {
        $host = $config->get('host');
        $dbName = $config->get('db_name');

        $this->dbm = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => [
                'default' => ['connection' => 'mysql'],
            ],
            'connections' => [
                'mysql' => [
                    'driver' => MySQLDriver::class,
                    'options' => [
                        'connection' => "mysql:host=$host;dbname=$dbName",
                        'username' => $config->get('user'),
                        'password' => $config->get('password'),
                    ],
                ],
            ],
        ]));
    }

    public function db(): DatabaseInterface
    {
        return $this->dbm->database();
    }
}
