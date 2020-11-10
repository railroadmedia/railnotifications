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
    'data_mode' => 'host',
    'database_name' => 'mydb',
    'database_user' => 'root',
    'database_password' => 'root',
    'database_host' => 'mysql',
    'database_driver' => 'pdo_mysql',
    'database_in_memory' => false,
    'enable_query_log' => false,
    'database_connection_name' => 'mysql',

    // entities
    'entities' => [
        [
            'path' => __DIR__ . '/../src/Entities',
            'namespace' => 'Railroad\Railnotifications\Entities',
        ],
    ],
    'emailAddressFrom' => 'system@pianote.com',
    'emailBrandFrom' => 'Pianote',
    'replyAddress' => 'suport@pianote.com',
    'newThreadPostSubject' => 'Pianote Forums - New Thread Post: ',
    'newLessonCommentReplySubject' => 'Pianote - New Lesson Comment Reply: ',
    'newLessonCommentLikedSubject' => 'Pianote - New Lesson Comment Like: ',
];