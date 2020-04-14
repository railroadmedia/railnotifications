- [railnotifications](#railnotifications)
  * [Install](#install)
  * [Package Configuration](#package-configuration)
  * [Notification API](#notification-api)
    + [Table: `notifications`](#table---notifications-)
    + [JSON Endpoints](#json-endpoints)
      - [Get user notifications.](#get-user-notifications)
        * [HTTP Request](#http-request)
        * [Request Parameters](#request-parameters)
        * [Request Example:](#request-example-)
        * [Response Example (200):](#response-example--200--)
      - [Create a new notification.](#create-a-new-notification)
        * [HTTP Request](#http-request-1)
        * [Request Parameters](#request-parameters-1)
        * [Validation Rules](#validation-rules)
        * [Request Example:](#request-example--1)
        * [Response Example (200):](#response-example--200---1)
      - [Sync notification](#sync-notification)
        * [HTTP Request](#http-request-2)
        * [Request Parameters](#request-parameters-2)
        * [Validation Rules](#validation-rules-1)
        * [Request Example:](#request-example--2)
        * [Response Example (200):](#response-example--200---2)
      - [Read a notification](#read-a-notification)
        * [HTTP Request](#http-request-3)
        * [Request Parameters](#request-parameters-3)
        * [Request Example:](#request-example--3)
        * [Response Example (200):](#response-example--200---3)
      - [Unread a notification](#unread-a-notification)
        * [HTTP Request](#http-request-4)
        * [Request Parameters](#request-parameters-4)
        * [Request Example:](#request-example--4)
        * [Response Example (200):](#response-example--200---4)
      - [Read all notifications for a user](#read-all-notifications-for-a-user)
        * [HTTP Request](#http-request-5)
        * [Permissions](#permissions)
        * [Request Parameters](#request-parameters-5)
        * [Request Example:](#request-example--5)
        * [Response Example (200):](#response-example--200---5)
      - [Delete a notification](#delete-a-notification)
        * [HTTP Request](#http-request-6)
        * [Request Parameters](#request-parameters-6)
        * [Request Example:](#request-example--6)
        * [Response Example (204):](#response-example--204--)
      - [Show notification](#show-notification)
        * [HTTP Request](#http-request-7)
        * [Request Parameters](#request-parameters-7)
        * [Request Example:](#request-example--7)
        * [Response Example (404):](#response-example--404--)
  * [railnotifications/count-read](#railnotifications-count-read)
    + [HTTP Request](#http-request-8)
    + [Permissions](#permissions-1)
    + [Request Parameters](#request-parameters-8)
    + [Request Example:](#request-example--8)
    + [Response Example (201):](#response-example--201--)
  * [railnotifications/count-unread](#railnotifications-count-unread)
    + [HTTP Request](#http-request-9)
    + [Permissions](#permissions-2)
    + [Request Parameters](#request-parameters-9)
    + [Request Example:](#request-example--9)
    + [Response Example (201):](#response-example--201---1)
  * [api/railnotifications/notifications](#api-railnotifications-notifications)
    + [HTTP Request](#http-request-10)
    + [Permissions](#permissions-3)
    + [Request Parameters](#request-parameters-10)
    + [Request Example:](#request-example--10)
    + [Response Example (200):](#response-example--200---6)
  * [api/railnotifications/notification](#api-railnotifications-notification)
    + [HTTP Request](#http-request-11)
    + [Permissions](#permissions-4)
    + [Request Parameters](#request-parameters-11)
    + [Validation Rules](#validation-rules-2)
    + [Request Example:](#request-example--11)
    + [Response Example (500):](#response-example--500--)
  * [api/railnotifications/sync-notification](#api-railnotifications-sync-notification)
    + [HTTP Request](#http-request-12)
    + [Permissions](#permissions-5)
    + [Request Parameters](#request-parameters-12)
    + [Validation Rules](#validation-rules-3)
    + [Request Example:](#request-example--12)
    + [Response Example (500):](#response-example--500---1)
  * [api/railnotifications/read/{id}](#api-railnotifications-read--id-)
    + [HTTP Request](#http-request-13)
    + [Permissions](#permissions-6)
    + [Request Parameters](#request-parameters-13)
    + [Request Example:](#request-example--13)
    + [Response Example (404):](#response-example--404---1)
  * [api/railnotifications/unread/{id}](#api-railnotifications-unread--id-)
    + [HTTP Request](#http-request-14)
    + [Permissions](#permissions-7)
    + [Request Parameters](#request-parameters-14)
    + [Request Example:](#request-example--14)
    + [Response Example (404):](#response-example--404---2)
  * [api/railnotifications/read-all/{id}](#api-railnotifications-read-all--id-)
    + [HTTP Request](#http-request-15)
    + [Permissions](#permissions-8)
    + [Request Parameters](#request-parameters-15)
    + [Request Example:](#request-example--15)
    + [Response Example (200):](#response-example--200---7)
  * [api/railnotifications/notification/{id}](#api-railnotifications-notification--id-)
    + [HTTP Request](#http-request-16)
    + [Permissions](#permissions-9)
    + [Request Parameters](#request-parameters-16)
    + [Request Example:](#request-example--16)
    + [Response Example (204):](#response-example--204---1)
  * [api/railnotifications/notification/{id}](#api-railnotifications-notification--id--1)
    + [HTTP Request](#http-request-17)
    + [Permissions](#permissions-10)
    + [Request Parameters](#request-parameters-17)
    + [Request Example:](#request-example--17)
    + [Response Example (404):](#response-example--404---3)
  * [api/railnotifications/count-read](#api-railnotifications-count-read)
    + [HTTP Request](#http-request-18)
    + [Permissions](#permissions-11)
    + [Request Parameters](#request-parameters-18)
    + [Request Example:](#request-example--18)
    + [Response Example (201):](#response-example--201---2)
  * [api/railnotifications/count-unread](#api-railnotifications-count-unread)
    + [HTTP Request](#http-request-19)
    + [Permissions](#permissions-12)
    + [Request Parameters](#request-parameters-19)
    + [Request Example:](#request-example--19)
    + [Response Example (201):](#response-example--201---3)
  * [Notification Broadcast API](#notification-broadcast-api)
- [Notification Broadcast API](#notification-broadcast-api-1)
- [JSON Endpoints](#json-endpoints-1)
  * [railnotifications/broadcast](#railnotifications-broadcast)
    + [HTTP Request](#http-request-20)
    + [Permissions](#permissions-13)
    + [Request Parameters](#request-parameters-20)
    + [Validation Rules](#validation-rules-4)
    + [Request Example:](#request-example--20)
    + [Response Example (500):](#response-example--500---2)
  * [railnotifications/broadcast/mark-succeeded/{id}](#railnotifications-broadcast-mark-succeeded--id-)
    + [HTTP Request](#http-request-21)
    + [Permissions](#permissions-14)
    + [Request Parameters](#request-parameters-21)
    + [Request Example:](#request-example--21)
    + [Response Example (404):](#response-example--404---4)
  * [railnotifications/broadcast/mark-failed/{id}](#railnotifications-broadcast-mark-failed--id-)
    + [HTTP Request](#http-request-22)
    + [Permissions](#permissions-15)
    + [Request Parameters](#request-parameters-22)
    + [Request Example:](#request-example--22)
    + [Response Example (404):](#response-example--404---5)
  * [railnotifications/broadcast/{id}](#railnotifications-broadcast--id-)
    + [HTTP Request](#http-request-23)
    + [Permissions](#permissions-16)
    + [Request Parameters](#request-parameters-23)
    + [Request Example:](#request-example--23)
    + [Response Example (404):](#response-example--404---6)
  * [api/railnotifications/broadcast](#api-railnotifications-broadcast)
    + [HTTP Request](#http-request-24)
    + [Permissions](#permissions-17)
    + [Request Parameters](#request-parameters-24)
    + [Validation Rules](#validation-rules-5)
    + [Request Example:](#request-example--24)
    + [Response Example (500):](#response-example--500---3)
  * [api/railnotifications/broadcast/mark-succeeded](#api-railnotifications-broadcast-mark-succeeded)
    + [HTTP Request](#http-request-25)
    + [Permissions](#permissions-18)
    + [Request Parameters](#request-parameters-25)
    + [Request Example:](#request-example--25)
    + [Response Example (500):](#response-example--500---4)
  * [api/railnotifications/broadcast/mark-failed](#api-railnotifications-broadcast-mark-failed)
    + [HTTP Request](#http-request-26)
    + [Permissions](#permissions-19)
    + [Request Parameters](#request-parameters-26)
    + [Request Example:](#request-example--26)
    + [Response Example (500):](#response-example--500---5)
  * [api/railnotifications/broadcast/{id}](#api-railnotifications-broadcast--id-)
    + [HTTP Request](#http-request-27)
    + [Permissions](#permissions-20)
    + [Request Parameters](#request-parameters-27)
    + [Request Example:](#request-example--27)
    + [Response Example (404):](#response-example--404---7)

<small><i><a href='http://ecotrust-canada.github.io/markdown-toc/'>Table of contents generated with markdown-toc</a></i></small>


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

#### Get user notifications.

##### HTTP Request
    `GET railnotifications/notifications`

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

##### Request Parameters
|Type|Key|Required|Notes|
|----|---|--------|-----|


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



##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


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
    `PUT railnotifications/read-all/{id}`


##### Permissions

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


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

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


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

##### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


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
 
