- [railnotifications](#railnotifications)
  * [Install](#install)
  * [Package Configuration](#package-configuration)
  * [API](#api)
    + [Tables:](#tables-)
    + [JSON Endpoints](#json-endpoints)
      - [Get user notifications.](#get-user-notifications)
        * [HTTP Request](#http-request)
        * [Mobile Request](#mobile-request)
        * [Request Parameters](#request-parameters)
        * [Request Example:](#request-example-)
        * [Response Example (200):](#response-example--200--)
      - [Create a new notification.](#create-a-new-notification)
        * [HTTP Request](#http-request-1)
        * [Mobile Request](#mobile-request-1)
        * [Request Parameters](#request-parameters-1)
        * [Validation Rules](#validation-rules)
        * [Request Example:](#request-example--1)
        * [Response Example (200):](#response-example--200---1)
      - [Sync notification](#sync-notification)
        * [HTTP Request](#http-request-2)
        * [Mobile Request](#mobile-request-2)
        * [Request Parameters](#request-parameters-2)
        * [Validation Rules](#validation-rules-1)
        * [Request Example:](#request-example--2)
        * [Response Example (200):](#response-example--200---2)
      - [Read a notification](#read-a-notification)
        * [HTTP Request](#http-request-3)
        * [Mobile Request](#mobile-request-3)
        * [Request Parameters](#request-parameters-3)
        * [Request Example:](#request-example--3)
        * [Response Example (200):](#response-example--200---3)
      - [Unread a notification](#unread-a-notification)
        * [HTTP Request](#http-request-4)
        * [Mobile Request](#mobile-request-4)
        * [Request Parameters](#request-parameters-4)
        * [Request Example:](#request-example--4)
        * [Response Example (200):](#response-example--200---4)
      - [Read all notifications for a user](#read-all-notifications-for-a-user)
        * [HTTP Request](#http-request-5)
        * [Mobile Request](#mobile-request-5)
        * [Request Parameters](#request-parameters-5)
        * [Request Example:](#request-example--5)
        * [Response Example (200):](#response-example--200---5)
      - [Delete a notification](#delete-a-notification)
        * [HTTP Request](#http-request-6)
        * [Mobile Request](#mobile-request-6)
        * [Request Parameters](#request-parameters-6)
        * [Request Example:](#request-example--6)
        * [Response Example (204):](#response-example--204--)
      - [Show notification](#show-notification)
        * [HTTP Request](#http-request-7)
        * [Mobile Request](#mobile-request-7)
        * [Request Parameters](#request-parameters-7)
        * [Request Example:](#request-example--7)
        * [Response Example (404):](#response-example--404--)
      - [Count all the notifications that are marked as readed](#count-all-the-notifications-that-are-marked-as-readed)
        * [HTTP Request](#http-request-8)
        * [Mobile Request](#mobile-request-8)
        * [Request Parameters](#request-parameters-8)
        * [Request Example:](#request-example--8)
        * [Response Example (201):](#response-example--201--)
      - [Count all the unread notifications](#count-all-the-unread-notifications)
        * [HTTP Request](#http-request-9)
        * [Mobile Request](#mobile-request-9)
        * [Request Parameters](#request-parameters-9)
        * [Request Example:](#request-example--9)
        * [Response Example (201):](#response-example--201---1)
      - [Broadcast notification on specified channels](#broadcast-notification-on-specified-channels)
        * [HTTP Request](#http-request-10)
        * [Mobile Request](#mobile-request-10)
        * [Request Parameters](#request-parameters-10)
        * [Validation Rules](#validation-rules-2)
        * [Request Example:](#request-example--10)
        * [Response Example (200):](#response-example--200---6)
      - [Mark broadcast as succeeded](#mark-broadcast-as-succeeded)
        * [HTTP Request](#http-request-11)
        * [Mobile Request](#mobile-request-11)
        * [Request Parameters](#request-parameters-11)
        * [Request Example:](#request-example--11)
        * [Response Example (404):](#response-example--404---1)
        * [Response Example (200)](#response-example--200-)
      - [Mark broadcast as failed](#mark-broadcast-as-failed)
        * [HTTP Request](#http-request-12)
        * [Mobile Request](#mobile-request-12)
        * [Permissions](#permissions)
        * [Request Parameters](#request-parameters-12)
        * [Request Example:](#request-example--12)
        * [Response Example (404):](#response-example--404---2)
        * [Response Example (200):](#response-example--200---7)
      - [Show broadcast](#show-broadcast)
        * [HTTP Request](#http-request-13)
        * [Mobile Request](#mobile-request-13)
        * [Permissions](#permissions-1)
        * [Request Parameters](#request-parameters-13)
        * [Request Example:](#request-example--13)
        * [Response Example (404):](#response-example--404---3)
        * [Response Example (200):](#response-example--200---8)



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

6. Create new provider for Content and Forum
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
 





 
