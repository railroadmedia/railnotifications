- [railnotifications](#railnotifications)
  * [Install](#install)
  * [Package Configuration](#package-configuration)
  * [API](#api)
    + [Tables:](#tables-)
    + [JSON Endpoints](#json-endpoints)
      - [Get user notifications.](#get-user-notifications)
      - [Create a new notification.](#create-a-new-notification)
      - [Sync notification](#sync-notification)
      - [Read a notification](#read-a-notification)
      - [Unread a notification](#unread-a-notification)
      - [Read all notifications for a user](#read-all-notifications-for-a-user)
      - [Delete a notification](#delete-a-notification)
      - [Show notification](#show-notification)
      - [Count all the notifications that are marked as readed](#count-all-the-notifications-that-are-marked-as-readed)
      - [Count all the unread notifications](#count-all-the-unread-notifications)
      - [Broadcast notification on specified channels](#broadcast-notification-on-specified-channels)
      - [Mark broadcast as succeeded](#mark-broadcast-as-succeeded)
      - [Mark broadcast as failed](#mark-broadcast-as-failed)
      - [Show broadcast](#show-broadcast)



# railnotifications
Railnotifications is an easy to use Laravel package for sending email and push notification with Firebase Cloud Messaging (FCM).


## Install

1. Install via composer: 
> composer require railroad/railnotifications:1.0
2. Add service provider to your application laravel config app.php file:

```php
'providers' => [
    
    // ... other providers
     Railroad\Railnotifications\NotificationsServiceProvider::class,
],
```
3. Publish the railnotifications config file: 
> php artisan vendor:publish
4. Fill the railnotifications.php config file:
```php

return array(
    'channels' => [
        'email' => \Railroad\Railnotifications\Channels\EmailChannel::class,
        'fcm' => \Railroad\Railnotifications\Channels\FcmChannel::class
    ],

    // cache
    'redis_host' => 'redis',
    'redis_port' => 6379,

    'development_mode' => true,

    // database
    'database_connection_name' => 'mysql',
    'database_name' => env('DB_DATABASE'),
    'database_user' => env('DB_USERNAME'),
    'database_password' => env('DB_PASSWORD'),
    'database_host' => env('DB_HOST'),
    'database_driver' => 'pdo_mysql',
    'database_in_memory' => false,
    'enable_query_log' => false,

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
    'newLessonCommentReplySubject' => 'Pianote - New Lesson Comment Reply: '
);
```
5. In the .env file should add the server key and the secret key for the Firebase Cloud Messaging:
```
FCM_SERVER_KEY=my_secret_server_key
FCM_SENDER_ID=my_secret_sender_id
````
The FCM keys can be find in Firebase (project settings -> cloud messaging) or in 1Password.

6. Create new provider for Content that implements `ContentProviderInterface` from Railnotifications package. The provider should contain the following methods: `getContentById` and `getCommentById`, like bellow: 
```php
<?php

namespace App\Providers;

use Railroad\Railcontent\Repositories\RepositoryBase;
use Railroad\Railcontent\Services\CommentService;
use Railroad\Railcontent\Services\ContentService;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;

class RailcontentContentProvider implements ContentProviderInterface
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * RailcontentContentProvider constructor.
     *
     * @param ContentService $contentService
     * @param CommentService $commentService
     */
    public function __construct(
        ContentService $contentService,
        CommentService $commentService
    ) {
        $this->contentService = $contentService;
        $this->commentService = $commentService;

        RepositoryBase::$connectionMask = null;
    }

    /**
     * @inheritDoc
     */
    public function getContentById($id)
    {
        return $this->contentService->getById($id);
    }

    /**
     * @inheritDoc
     */
    public function getCommentById($id)
    {
        return $this->commentService->get($id);
    }
}

```

7. Create new provider for Forum that implements `RailforumProviderInterface` from Railnotifications package. The provider should contain the following methods: `getPostById`, `getThreadById` and `getThreadFollowerIds`, like bellow: 

```php
<?php

namespace App\Providers;

use Railroad\Railforums\Repositories\PostRepository;
use Railroad\Railforums\Repositories\ThreadFollowRepository;
use Railroad\Railforums\Repositories\ThreadRepository;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;

class RailforumProvider implements RailforumProviderInterface
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var ThreadRepository
     */
    private $threadRepository;

    /**
     * @var ThreadFollowRepository
     */
    private $threadFollowRepository;

    /**
     * RailforumProvider constructor.
     *
     * @param PostRepository $postRepository
     * @param ThreadRepository $threadRepository
     * @param ThreadFollowRepository $threadFollowRepository
     */
    public function __construct(
        PostRepository $postRepository,
        ThreadRepository $threadRepository,
        ThreadFollowRepository $threadFollowRepository
    ) {
        $this->postRepository = $postRepository;
        $this->threadRepository = $threadRepository;
        $this->threadFollowRepository = $threadFollowRepository;
    }

    /**
     * @param int $postId
     * @return array|\Railroad\Resora\Entities\Entity|null
     */
    public function getPostById($postId)
    {
        return $this->postRepository->read($postId);
    }

    /**
     * @param int $threadId
     * @return array|\Railroad\Resora\Entities\Entity|null
     */
    public function getThreadById($threadId)
    {
        return $this->threadRepository->read($threadId);
    }

    /**
     * @param $threadId
     * @return mixed
     */
    public function getThreadFollowerIds($threadId)
    {
        return $this->threadFollowRepository->getThreadFollowerIds($threadId)->toArray();
    }
}

```
8. Create new provider for Forum that implements `UserProviderInterface` from Railnotifications package. The provider should contain the following methods: `getRailnotificationsUserById`, `getUserFirebaseTokens`, `deleteUserFirebaseTokens` and `updateUserFirebaseToken`, like bellow: 

```php
<?php

namespace App\Providers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Railroad\Ecommerce\Entities\User;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\User as RailnotificationUser;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\UserFirebaseTokensRepository;
use Railroad\Usora\Repositories\UserRepository;

class RailnotificationsUserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserFirebaseTokensRepository
     */
    private $userFirebaseTokenRepository;

    /**
     * @var UsoraEntityManager
     */
    private $usoraEntityManager;

    /**
     * RailnotificationsUserProvider constructor.
     *
     * @param UsoraEntityManager $usoraEntityManager
     * @param UserRepository $userRepository
     * @param UserFirebaseTokensRepository $userFirebaseTokensRepository
     */
    public function __construct(
        UsoraEntityManager $usoraEntityManager,
        UserRepository $userRepository,
        UserFirebaseTokensRepository $userFirebaseTokensRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userFirebaseTokenRepository = $userFirebaseTokensRepository;
        $this->usoraEntityManager = $usoraEntityManager;
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function getRailnotificationsUserById(int $id)
    : ?RailnotificationUser {
        $usoraUser = $this->userRepository->find($id);

        if ($usoraUser) {
            return new RailnotificationUser(
                $usoraUser->getId(),
                $usoraUser->getEmail(),
                $usoraUser->getDisplayName(),
                $usoraUser->getProfilePictureUrl()
            );
        }

        return null;
    }

    /**
     * @param int $userId
     * @param string $type
     * @return array|null
     */
    public function getUserFirebaseTokens(int $userId, $type = null)
    : ?array {

        $criteria = [
            'user' => $userId,
        ];

        if ($type) {
            $criteria += [
                'type' => $type,
            ];
        }

        return $this->userFirebaseTokenRepository->findBy($criteria);
    }

    /**
     * @param int $userId
     * @param array $tokens
     * @return mixed|void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteUserFirebaseTokens(int $userId, array $tokens)
    {
        $qb = $this->userFirebaseTokenRepository->createQueryBuilder('f');
        $qb->where('f.user = :user')
            ->andWhere('f.token IN (:tokens)')
            ->setParameters(
                [
                    'user' => $userId,
                    'tokens' => $tokens,
                ]
            );

        $userFirebaseTokens =
            $qb->getQuery()
                ->getResult();

        foreach ($userFirebaseTokens as $userFirebaseToken) {
            $this->usoraEntityManager->remove($userFirebaseToken);
            $this->usoraEntityManager->flush();
        }
    }

    /**
     * @param $userId
     * @param $oldToken
     * @param $newToken
     * @return mixed
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateUserFirebaseToken($userId, $oldToken, $newToken)
    {
        $qb = $this->userFirebaseTokenRepository->createQueryBuilder('f');
        $qb->where('f.user = :user')
            ->andWhere('f.token = :token')
            ->setParameters(
                [
                    'user' => $userId,
                    'token' => $oldToken,
                ]
            );
        $userFirebaseToken =
            $qb->getQuery()
                ->getResult();

        $userFirebaseToken->setToken($newToken);

        $this->usoraEntityManager->persist($userFirebaseToken);
        $this->usoraEntityManager->flush();

        return $userFirebaseToken;
    }
}

```
9. In AppServiceProvider boot method create instance for the providers: 
```php
        app()->instance(\Railroad\Railnotifications\Contracts\UserProviderInterface::class, app()->make(RailnotificationsUserProvider::class));
       
        app()->instance(\Railroad\Railnotifications\Contracts\ContentProviderInterface::class, app()->make(RailcontentContentProvider::class));

        app()->instance(\Railroad\Railnotifications\Contracts\RailforumProviderInterface::class, app()->make(RailforumProvider::class));
```
## API 

### Tables: 
`notifications`

| Column | Data Type | Attributes | Default | Description |
| --- | --- | --- | --- | --- |
| `id` | INT(10) UNSIGNED | Primary, Auto increment, Not null |  |  |
| `type` | VARCHAR(255) | Not null |  |  |
| `data` | TEXT | Not null |  |  |
| `recipient_id` | INT(11) | Null |  |  |
| `read_on` | DATETIME |  | NULL |  |
| `created_at` | DATETIME | Not null |  |  |
| `updated_at` | DATETIME |  | NULL |  |

`notification_broadcasts`
| Column | Data Type | Attributes | Default | Description |
| --- | --- | --- | --- | --- |
| `id` | INT(10) UNSIGNED | Primary, Auto increment, Not null |  |  |
| `channel` | VARCHAR(255) | Not null |  |  |
| `type` | VARCHAR(255) | Not null |  |  |
| `status` | VARCHAR(255) | Not null |  |  |
| `report` | TEXT | Null |  |  |
| `notification_id` | INT(11) | Not null |  |  |
| `aggregation_group_id` |  VARCHAR(255) | Null |  |  |
| `broadcast_on` | DATETIME |  | NULL |  |
| `created_at` | DATETIME | Not null |  |  |
| `updated_at` | DATETIME |  | NULL |  |

### JSON Endpoints

#### Get user notifications.

##### HTTP Request
    `GET railnotifications/notifications`
    
##### Mobile Request
    `GET api/railnotifications/notifications`

##### Request Parameters

|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|user_id|    |IF user_id it's set on the request only the notifications for the specified user are returned, otherwise the notifications for authenticated user are returned.|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notifications',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (200):

```json
{
   "data":[
      {
         "id":3,
         "type":"lesson comment reply",
         "data":{
            "commentId":212738214
         },
         "read_on":null,
         "created_at":"2020-04-10 14:31:27",
         "updated_at":null,
         "recipient":{
            "id":1
         }
      },
      {
         "id":4,
         "type":"lesson comment reply",
         "data":{
            "commentId":8587559,
         },
         "read_on":null,
         "created_at":"2020-04-10 14:31:27",
         "updated_at":null,
         "recipient":{
            "id":1
         }
      },
      {
         "id":5,
         "type":"lesson comment reply",
         "data":{
            "commentId":58997,
         },
         "read_on":null,
         "created_at":"2020-04-10 14:31:27",
         "updated_at":null,
         "recipient":{
            "id":1
         }
      }
   ]
}
```

#### Create a new notification.

##### HTTP Request
    `PUT railnotifications/notification`

##### Mobile Request
    `PUT api/railnotifications/notification`

##### Request Parameters
|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|type|  Yes  |Notification type.|
|body|data|  Yes  |Array with comment id or thread id (depends on the notification type)|
|body|recipient_id|Yes   |The user that should receive the notification|

##### Validation Rules
```php
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
```

##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notification',
{
    "type": "lesson comment reply",
    "data":{
      "commentId":1082,
    },
    "recipient_id": "1"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (200):

```json
{
   "id":1,
   "type":"lesson comment reply",
   "data":{
      "commentId":1082,
   },
   "read_on":null,
   "created_at":"2020-04-10 14:39:01",
   "updated_at":"2020-04-10 14:39:01",
   "recipient":{
      "id":1
   }
}
```

#### Sync notification
If not exists it will be created, if exists will be updated.
##### HTTP Request
    `PUT railnotifications/sync-notification`
##### Mobile Request
    `PUT api/railnotifications/sync-notification`

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|type| Yes   |Notification type.|
|body|data|  Yes  ||
|body|recipient_id|   Yes ||

##### Validation Rules
```php
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
```

##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/sync-notification',
{
    "type": "lesson comment reply",
    "data":{
      "commentId":10802,
    },
    "recipient_id": "1"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (200):

```json
{
   "id":1,
   "type":"lesson comment reply",
   "data":{
      "commentId":10802,
   },
   "read_on":null,
   "created_at":"2020-04-10 14:39:01",
   "updated_at":"2020-04-10 14:39:01",
   "recipient":{
      "id":1
   }
}
```

#### Read a notification 
##### HTTP Request
    `PUT railnotifications/read/{id}`
##### Mobile Request
    `PUT api/railnotifications/read/{id}`

##### Request Parameters
|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|id|yes|Id of the notification you want to mark as read.|
|body|read_on_date_time|no|The date that it's set on the notification read on field. If not exists current date it's set.|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/read/1',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (200):

```json
{
    "id": 1,
    "type": "comment reply",
    "data": [],
    "read_on": "2020-04-10 13:55:00",
    "created_at": "2006-01-02 10:44:10",
    "updated_at": "2020-04-10 13:55:00"
}
```

#### Unread a notification

##### HTTP Request

    `PUT railnotifications/unread/{id}`
##### Mobile Request
    `PUT api/railnotifications/unread/{id}`


##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|id|yes|Id of notification you want to mark as unread|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/unread/1',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (200):

```json
{
    "id": 1,
    "type": "comment reply",
    "data": [],
    "read_on": null,
    "created_at": "2006-01-02 10:44:10",
    "updated_at": "2020-04-10 13:55:00"
}
```

#### Read all notifications for a user

##### HTTP Request
    `PUT railnotifications/read-all/{userId}`
##### Mobile Request
    `PUT api/railnotifications/read-all/{userId}`

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|userId|yes|All the notifications for specified user id will be marked as read|
|body|read_on_date_time|no|The date that it's set on the notification read on field. If not exists current date it's set.|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/read-all/1',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (200):

```json
{
    "data": []
}
```

#### Delete a notification

##### HTTP Request
    `DELETE railnotifications/notification/{id}`
##### Mobile Request
    `DELETE api/railnotifications/notification/{id}`

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|id|yes|Id of the notification you want to delete|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notification/1',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (204):

```json
null
```

#### Show notification

##### HTTP Request
    `GET railnotifications/notification/{id}`
##### Mobile Request
    `GET api/railnotifications/notification/{id}`

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|id|yes|Id of the notification|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notification/1',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (404):

```json
{
    "errors": {
        "title": "Not found.",
        "detail": "Update failed, notification not found with id: 1"
    }
}
```

#### Count all the notifications that are marked as readed 

##### HTTP Request
    `GET railnotifications/count-read`
##### Mobile Request
    `GET api/railnotifications/count-read`

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|user_id|no|If the user_id exists the notifications for the specified id are counted, otherwise the notifications for the authenticated user are counted|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/count-read',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (201):

```json
{
    "data": "10"
}
```

#### Count all the unread notifications

##### HTTP Request
    `GET railnotifications/count-unread`
##### Mobile Request
    `GET api/railnotifications/count-unread`

##### Request Parameters

|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|user_id|no|If the user_id exists the notifications for the specified id are counted, otherwise the notifications for the authenticated user are counted|


##### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/count-unread',
    success: function(response) {},
    error: function(response) {}
});
```

##### Response Example (201):

```json
{
    "data": "2"
}
```

#### Broadcast notification on specified channels
 
##### HTTP Request
     `PUT railnotifications/broadcast`
##### Mobile Request
     `PUT api/railnotifications/broadcast`
 
##### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 |body|notification_id|  Yes  |Notification id.|
 |body|channel| Yes   |The broadcast channel name. E.g: `email` or `fcm`|
 
##### Validation Rules

 ```php
         return [
             'channel' => 'string|required',
             'notification_id' => 'required',
         ];
 ```
 
 ##### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast',
 {
     "notification_id": "1",
     "channel": "fcm"
 }
    ,
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ##### Response Example (200):
 
 ```json
{
   "id":1,
   "channel":"fcm",
   "type":"single",
   "status":"sent",
   "report":null,
   "aggregation_group_id":null,
   "broadcast_on":"2020-04-14 09:34:27",
   "created_at":"2020-04-14 09:34:27",
   "updated_at":"2020-04-14 09:34:27",
   "notification":{
      "id":1,
      "type":"comment reply",
      "data":{
         "commentId":943
      },
      "read_on":null,
      "created_at":"2020-04-14 09:34:27",
      "updated_at":null,
      "recipient":{
         "id":1
      }
   }
}
 ```
 

 #### Mark broadcast as succeeded
 
 ##### HTTP Request
     `PUT railnotifications/broadcast/mark-succeeded/{id}`
##### Mobile Request
     `PUT api/railnotifications/broadcast/mark-succeeded/{id}`
 
 ##### Request Parameters
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 |query|id|yes|Broadcast id|
 
 
 ##### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast/mark-succeeded/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ##### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Mark as succeeded failed, notification broadcast not found with id: 1"
     }
 }
 ```
 
 ##### Response Example (200)
 ```json
 {
   "id":1,
   "channel":"email",
   "type":"single",
   "status":"sent",
   "report":null,
   "aggregation_group_id":null,
   "broadcast_on":"2020-04-14 09:39:05",
   "created_at":"2020-04-14 09:39:05",
   "updated_at":"2020-04-14 09:39:05",
   "notification":{
      "id":1,
      "type":"comment reply.",
      "data":{
         "commentId":81912
      },
      "read_on":null,
      "created_at":"2020-04-14 09:39:05",
      "updated_at":null,
      "recipient":{
         "id":1
      }
   }
}
 ```

 #### Mark broadcast as failed
 
 ##### HTTP Request
     `PUT railnotifications/broadcast/mark-failed/{id}`
 ##### Mobile Request
      `PUT api/railnotifications/broadcast/mark-failed/{id}`
      
 ##### Permissions
 
 ##### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 |query|id|yes|Broadcast id|
 
 
 ##### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast/mark-failed/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ##### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Notification broadcast not found with id: 1"
     }
 }
 ```
 ##### Response Example (200):
 ```json
 {
   "id":1,
   "channel":"email",
   "type":"single",
   "status":"failed",
   "report":"Doloremque ipsum deserunt consectetur. Earum tempora placeat hic. Maxime eveniet reiciendis corporis non earum non. Enim provident ut et voluptas quas praesentium magni.",
   "aggregation_group_id":null,
   "broadcast_on":"2020-04-14 09:45:04",
   "created_at":"2020-04-14 09:45:04",
   "updated_at":"2020-04-14 09:45:04",
   "notification":{
      "id":1,
      "type":"comment reply",
      "data":{
         "commentId":154
      },
      "read_on":null,
      "created_at":"2020-04-14 09:45:04",
      "updated_at":null,
      "recipient":{
         "id":1
      }
   }
}
 ```

 #### Show broadcast
 
 ##### HTTP Request
     `GET railnotifications/broadcast/{id}`
 ##### Mobile Request
      `GET api/railnotifications/broadcast/{id}`
 
 ##### Permissions
 
 ##### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 |query|id|yes||
 
 
 ##### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ##### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Notification broadcast not found with id: 1"
     }
 }
 ```
 
 ##### Response Example (200):
 ```json
 {
   "id":1,
   "channel":"fcm",
   "type":"single",
   "status":"sent",
   "report":null,
   "aggregation_group_id":null,
   "broadcast_on":"2020-04-14 09:48:09",
   "created_at":"2020-04-14 09:48:09",
   "updated_at":null,
   "notification":{
      "id":1,
      "type":"comment reply",
      "data":{
         "commentId":3881
      },
      "read_on":null,
      "created_at":"2020-04-14 09:48:09",
      "updated_at":null,
      "recipient":{
         "id":1
      }
   }
}
 ```
 





 
