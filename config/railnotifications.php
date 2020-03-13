<?php

return array(
    'channels' => [
        /* add custom channels here, custom channels must extend a base channel */

        /* Ex: 'mail' => \My\MailClass\::class, */
    ],

    // cache
    'redis_host' => 'redis',
    'redis_port' => 6379,

    'development_mode' => true,

    // database
    'database_name' => 'mydb',
    'database_user' => 'root',
    'database_password' => 'root',
    'database_host' => 'mysql',
    'database_driver' => 'pdo_mysql',
    'database_in_memory' => false,

    // entities
    'entities' => [
        [
            'path' => __DIR__ . '/../src/Entities',
            'namespace' => 'Railroad\Railnotifications\Entities',
        ],
    ],
);