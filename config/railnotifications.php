<?php

return [
    //the notifications will be broadcast on all the channels defined bellow
    'channels' => [
        'email' => \Railroad\Railnotifications\Channels\EmailChannel::class,
        'fcm' => \Railroad\Railnotifications\Channels\FcmChannel::class,
    ],

    // brand
    'brand' => 'brand',

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
    'enable_query_log' => false,
    'database_connection_name' => 'mysql',

    'data_mode' => 'host',


    // entities
    'entities' => [
        [
            'path' => __DIR__ . '/../src/Entities',
            'namespace' => 'Railroad\Railnotifications\Entities',
        ],
    ],

    // email details
    'email_address_from' => 'system@pianote.com',
    'email_brand_from' => 'Pianote',
    'email_reply_address' => 'suport@pianote.com',

    'mapping_types' => [
        'forum post in followed thread' => 'thread-reply',
        'forum post reply' => 'forum-reply',
        'lesson comment reply' => 'comment-reply',
        'lesson comment liked' => 'comment-like',
        'forum post liked' => 'forum-like',
    ],

    'api_middleware' => [
    ],
];