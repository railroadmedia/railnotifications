# railnotifications
========================================================================================================================
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
4. Fill the railnotifications.php config file

## Package Configuration

In the .env file should add the server key and the secret key for the Firebase Cloud Messaging:
```
FCM_SERVER_KEY=my_secret_server_key
FCM_SENDER_ID=my_secret_sender_id
````
The FCM keys can be find in Firebase (project settings -> cloud messaging) or in 1Password.

## Notification API 

### Table: `notifications`

| Column | Data Type | Attributes | Default | Description |
| --- | --- | --- | --- | --- |
| `id` | INT(10) UNSIGNED | Primary, Auto increment, Not null |  |  |
| `type` | VARCHAR(255) | Not null |  |  |
| `data` | TEXT | Not null |  |  |
| `recipient_id` | INT(11) | Null |  |  |
| `read_on` | DATETIME |  | NULL |  |
| `created_at` | DATETIME | Not null |  |  |
| `updated_at` | DATETIME |  | NULL |  |

### JSON Endpoints

## railnotifications/notifications
Get user notifications.

### HTTP Request
    `GET railnotifications/notifications`

### Request Parameters

|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|user_id|    |IF user_id it's set on the request only the notifications for the specified user are returned, otherwise the notifications for authenticated user are returned.|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notifications',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
   "data":[
      {
         "id":3,
         "type":"lesson comment reply",
         "data":{
            "commentId":212738214,
            "content":{
               "id":99
            },
            "originalComment":{
               "id":4887133
            }
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
            "content":{
               "id":629
            },
            "originalComment":{
               "id":29
            }
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
            "content":{
               "id":67540
            },
            "originalComment":{
               "id":466
            }
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

## railnotifications/notification
Create a new notification.
### HTTP Request
    `PUT railnotifications/notification`

### Request Parameters
|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|type|  Yes  |Notification type.|
|body|data|  Yes  |Data regarding the comment or thread|
|body|recipient_id|Yes   |The user that should receive the notification|

### Validation Rules
```php
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
```

### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notification',
{
    "type": "lesson comment reply",
    "data":{
      "commentId":1080185162,
      "content":{
         "id":185576414
      }
    },
    "recipient_id": "1"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
   "id":1,
   "type":"lesson comment reply",
   "data":{
      "commentId":1080185162,
      "content":{
         "id":185576414
      }
   },
   "read_on":null,
   "created_at":"2020-04-10 14:39:01",
   "updated_at":"2020-04-10 14:39:01",
   "recipient":{
      "id":1
   }
}
```

## railnotifications/sync-notification
Sync a notification: if not exists it will be created, if exists will be updated.
### HTTP Request
    `PUT railnotifications/sync-notification`

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|type| Yes   |Notification type.|
|body|data|  Yes  ||
|body|recipient_id|   Yes ||

### Validation Rules
```php
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
```

### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/sync-notification',
{
    "type": "lesson comment reply",
    "data":{
      "commentId":1080185162,
      "content":{
         "id":185576414
      }
    },
    "recipient_id": "1"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
   "id":1,
   "type":"lesson comment reply",
   "data":{
      "commentId":1080185162,
      "content":{
         "id":185576414
      }
   },
   "read_on":null,
   "created_at":"2020-04-10 14:39:01",
   "updated_at":"2020-04-10 14:39:01",
   "recipient":{
      "id":1
   }
}
```

## railnotifications/read/{id}
Mark notification as read
### HTTP Request
    `PUT railnotifications/read/{id}`

### Request Parameters
|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/read/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

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




<!-- END_ad7948a32ea52914e5f88f68e14c6105 -->

<!-- START_07e82af2d5f59d806eb25588b054b480 -->
## railnotifications/unread/{id}

### HTTP Request
    `PUT railnotifications/unread/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/unread/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

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




<!-- END_07e82af2d5f59d806eb25588b054b480 -->

<!-- START_c0da64a2b2c1c1bfe484cf98d0b52a9c -->
## railnotifications/read-all/{id}

### HTTP Request
    `PUT railnotifications/read-all/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/read-all/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": []
}
```




<!-- END_c0da64a2b2c1c1bfe484cf98d0b52a9c -->

<!-- START_ded1a96cd2e19182b1a9f665d58ac327 -->
## railnotifications/notification/{id}

### HTTP Request
    `DELETE railnotifications/notification/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notification/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (204):

```json
null
```




<!-- END_ded1a96cd2e19182b1a9f665d58ac327 -->

<!-- START_f61fad23cfef0123880ac7a87d60dfb5 -->
## railnotifications/notification/{id}

### HTTP Request
    `GET railnotifications/notification/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/notification/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (404):

```json
{
    "errors": {
        "title": "Not found.",
        "detail": "Update failed, notification not found with id: 1"
    }
}
```




<!-- END_f61fad23cfef0123880ac7a87d60dfb5 -->

<!-- START_8a3aa539a0a37b8deb4ed6111740f1b5 -->
## railnotifications/count-read

### HTTP Request
    `GET railnotifications/count-read`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/count-read',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (201):

```json
{
    "data": "0"
}
```




<!-- END_8a3aa539a0a37b8deb4ed6111740f1b5 -->

<!-- START_8f96b7a86188153807da7ee2aea64daf -->
## railnotifications/count-unread

### HTTP Request
    `GET railnotifications/count-unread`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/railnotifications/count-unread',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (201):

```json
{
    "data": "0"
}
```




<!-- END_8f96b7a86188153807da7ee2aea64daf -->

<!-- START_e27df8dfff6d7303d23aec5af1f3a1e9 -->
## api/railnotifications/notifications

### HTTP Request
    `GET api/railnotifications/notifications`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/notifications',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": []
}
```




<!-- END_e27df8dfff6d7303d23aec5af1f3a1e9 -->

<!-- START_1fa1bb9b9556514c712e24aca05674cc -->
## api/railnotifications/notification

### HTTP Request
    `PUT api/railnotifications/notification`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|type|    |Notification type.|
|body|data|    ||
|body|recipient_id|    ||

### Validation Rules
```php
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
```

### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/notification',
{
    "type": "lesson comment reply",
    "data": "array",
    "recipient_id": "1"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (500):

```json
{
    "message": "Server Error"
}
```




<!-- END_1fa1bb9b9556514c712e24aca05674cc -->

<!-- START_24c538e86b858669ecd5686c3c6738ce -->
## api/railnotifications/sync-notification

### HTTP Request
    `PUT api/railnotifications/sync-notification`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|type|    |Notification type.|
|body|data|    ||
|body|recipient_id|    ||

### Validation Rules
```php
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
```

### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/sync-notification',
{
    "type": "lesson comment reply",
    "data": "array",
    "recipient_id": "1"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (500):

```json
{
    "message": "Server Error"
}
```




<!-- END_24c538e86b858669ecd5686c3c6738ce -->

<!-- START_d41e0754ac49ae07d08b91f9924c5d16 -->
## api/railnotifications/read/{id}

### HTTP Request
    `PUT api/railnotifications/read/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/read/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (404):

```json
{
    "errors": {
        "title": "Not found.",
        "detail": "Mark as read failed, notification not found with id: 1"
    }
}
```




<!-- END_d41e0754ac49ae07d08b91f9924c5d16 -->

<!-- START_e3d95e3e5dc2c464750b7c7cc2f2ab5c -->
## api/railnotifications/unread/{id}

### HTTP Request
    `PUT api/railnotifications/unread/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/unread/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (404):

```json
{
    "errors": {
        "title": "Not found.",
        "detail": "Mark as read failed, notification not found with id: 1"
    }
}
```




<!-- END_e3d95e3e5dc2c464750b7c7cc2f2ab5c -->

<!-- START_51a1a768a1fb9816252c72690c946596 -->
## api/railnotifications/read-all/{id}

### HTTP Request
    `PUT api/railnotifications/read-all/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/read-all/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": []
}
```




<!-- END_51a1a768a1fb9816252c72690c946596 -->

<!-- START_9435efd23b86bf0872aee8a27d2a642a -->
## api/railnotifications/notification/{id}

### HTTP Request
    `DELETE api/railnotifications/notification/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/notification/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (204):

```json
null
```




<!-- END_9435efd23b86bf0872aee8a27d2a642a -->

<!-- START_5ab0ef344fea947e55303d52cc709f45 -->
## api/railnotifications/notification/{id}

### HTTP Request
    `GET api/railnotifications/notification/{id}`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/notification/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (404):

```json
{
    "errors": {
        "title": "Not found.",
        "detail": "Update failed, notification not found with id: 1"
    }
}
```




<!-- END_5ab0ef344fea947e55303d52cc709f45 -->

<!-- START_7bb183753565535856ffecc39650f1ea -->
## api/railnotifications/count-read

### HTTP Request
    `GET api/railnotifications/count-read`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/count-read',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (201):

```json
{
    "data": "0"
}
```




<!-- END_7bb183753565535856ffecc39650f1ea -->

<!-- START_ff3ce72cd2945b060f782ff8991d75b4 -->
## api/railnotifications/count-unread

### HTTP Request
    `GET api/railnotifications/count-unread`


### Permissions

### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/api/railnotifications/count-unread',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (201):

```json
{
    "data": "0"
}
```




<!-- END_ff3ce72cd2945b060f782ff8991d75b4 -->





## Notification Broadcast API

 # Notification Broadcast API
 
 # JSON Endpoints
 
 
 <!-- START_da669914afb4f3fdeb108600e0cdd562 -->
 ## railnotifications/broadcast
 
 ### HTTP Request
     `PUT railnotifications/broadcast`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 |body|notification_id|    |Notification id.|
 |body|channel|    ||
 
 ### Validation Rules
 ```php
         return [
             'channel' => 'string',
             'notification_id' => 'required',
         ];
 ```
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast',
 {
     "notification_id": "1",
     "channel": "vero"
 }
    ,
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (500):
 
 ```json
 {
     "message": "Server Error"
 }
 ```
 
 
 
 
 <!-- END_da669914afb4f3fdeb108600e0cdd562 -->
 
 <!-- START_4c4954ef496e4a6fe3c1c24dc78b757c -->
 ## railnotifications/broadcast/mark-succeeded/{id}
 
 ### HTTP Request
     `PUT railnotifications/broadcast/mark-succeeded/{id}`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast/mark-succeeded/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Mark as succeeded failed, notification broadcast not found with id: 1"
     }
 }
 ```
 
 
 
 
 <!-- END_4c4954ef496e4a6fe3c1c24dc78b757c -->
 
 <!-- START_404129e76142f21ae62b6e3a31f48e5e -->
 ## railnotifications/broadcast/mark-failed/{id}
 
 ### HTTP Request
     `PUT railnotifications/broadcast/mark-failed/{id}`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast/mark-failed/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Notification broadcast not found with id: 1"
     }
 }
 ```
 
 
 
 
 <!-- END_404129e76142f21ae62b6e3a31f48e5e -->
 
 <!-- START_af86e7d26e1b540dee093b2d33070c3b -->
 ## railnotifications/broadcast/{id}
 
 ### HTTP Request
     `GET railnotifications/broadcast/{id}`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/railnotifications/broadcast/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Notification broadcast not found with id: 1"
     }
 }
 ```
 
 
 
 
 <!-- END_af86e7d26e1b540dee093b2d33070c3b -->
 
 <!-- START_e865820cb971fda736367dabd76a0372 -->
 ## api/railnotifications/broadcast
 
 ### HTTP Request
     `PUT api/railnotifications/broadcast`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 |body|notification_id|    |Notification id.|
 |body|channel|    ||
 
 ### Validation Rules
 ```php
         return [
             'channel' => 'string',
             'notification_id' => 'required',
         ];
 ```
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/api/railnotifications/broadcast',
 {
     "notification_id": "1",
     "channel": "quia"
 }
    ,
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (500):
 
 ```json
 {
     "message": "Server Error"
 }
 ```
 
 
 
 
 <!-- END_e865820cb971fda736367dabd76a0372 -->
 
 <!-- START_34f07ca3168d14a9aa6c0ae55a9feb7b -->
 ## api/railnotifications/broadcast/mark-succeeded
 
 ### HTTP Request
     `PUT api/railnotifications/broadcast/mark-succeeded`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/api/railnotifications/broadcast/mark-succeeded',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (500):
 
 ```json
 {
     "message": "Server Error"
 }
 ```
 
 
 
 
 <!-- END_34f07ca3168d14a9aa6c0ae55a9feb7b -->
 
 <!-- START_e5c891c8cb009cf2af073814bf209068 -->
 ## api/railnotifications/broadcast/mark-failed
 
 ### HTTP Request
     `PUT api/railnotifications/broadcast/mark-failed`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/api/railnotifications/broadcast/mark-failed',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (500):
 
 ```json
 {
     "message": "Server Error"
 }
 ```
 
 
 
 
 <!-- END_e5c891c8cb009cf2af073814bf209068 -->
 
 <!-- START_58b5da8fe0e830b0ee275b5e14296c94 -->
 ## api/railnotifications/broadcast/{id}
 
 ### HTTP Request
     `GET api/railnotifications/broadcast/{id}`
 
 
 ### Permissions
 
 ### Request Parameters
 
 
 |Type|Key|Required|Notes|
 |----|---|--------|-----|
 
 
 ### Request Example:
 
 ```js
 $.ajax({
     url: 'https://www.domain.com' +
              '/api/railnotifications/broadcast/1',
     success: function(response) {},
     error: function(response) {}
 });
 ```
 
 ### Response Example (404):
 
 ```json
 {
     "errors": {
         "title": "Not found.",
         "detail": "Notification broadcast not found with id: 1"
     }
 }
 ```
 
 
 
 
 <!-- END_58b5da8fe0e830b0ee275b5e14296c94 -->
 
