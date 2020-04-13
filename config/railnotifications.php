<?php

return [
    'channels' => [
        /* add custom channels here, custom channels must extend a base channel */
        \Railroad\Railnotifications\Channels\FcmChannel::class
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
    'data' => [
        \Railroad\Railnotifications\Entities\Notification::TYPE_LESSON_COMMENT_LIKED =>[
            'title' => 'New reply to your comment',
            'message' => 'New reply to your comment'
        ],
        \Railroad\Railnotifications\Entities\Notification::TYPE_LESSON_COMMENT_LIKED => [
            'title' => 'New like to your comment',
            'message' => 'New like to your comment'
        ]
    ],
    'emailAddressFrom' => 'system@pianote.com',
    'emailBrandFrom' => 'Pianote',
    'replyAddress' => 'suport@pianote.com',
    'newThreadPostSubject' => 'Pianote Forums - New Thread Post: ',
    'newLessonCommentReplySubject' => 'Pianote - New Lesson Comment Reply: '
];