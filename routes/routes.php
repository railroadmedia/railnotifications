<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => 'railnotifications',
        'middleware' => config('railnotifications.user_routes_middleware'),
    ],
    function () {

        Route::get(
            '/notifications',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@index'
        )
            ->name('notifications.index');

        Route::put(
            '/notification',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@store'
        )
            ->name('notification.store');

        Route::put(
            '/sync-notification',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@syncNotification'
        )
            ->name('notification.sync');

        Route::put(
            '/read/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@markAsRead'
        )
            ->name('notification.read');

        Route::put(
            '/unread/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@markAsUnRead'
        )
            ->name('notification.unread');

        Route::put(
            '/read-all',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@markAllAsRead'
        )
            ->name('notification.read.all');

        Route::delete(
            '/notification/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@delete'
        )
            ->name('notification.delete');

        Route::get(
            '/notification/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@showNotification'
        )
            ->name('notification.show');

        Route::get(
            '/count-read',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@countReadNotifications'
        )
            ->name('notification.count.read');

        Route::get(
            '/count-unread',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@countUnReadNotifications'
        )
            ->name('notification.count.unread');

        // Broadcast notifications
        Route::put(
            '/broadcast',
            \Railroad\Railnotifications\Controllers\BroadcastNotificationJsonController::class . '@broadcast'
        )
            ->name('notification.broadcast');

        Route::put(
            '/broadcast/mark-succeeded/{id}',
            \Railroad\Railnotifications\Controllers\BroadcastNotificationJsonController::class . '@markAsSucceeded'
        )
            ->name('notification.broadcast.mark-succeeded');

        Route::put(
            '/broadcast/mark-failed/{id}',
            \Railroad\Railnotifications\Controllers\BroadcastNotificationJsonController::class . '@markAsFailed'
        )
            ->name('notification.broadcast.mark-failed');

        Route::get(
            '/broadcast/{id}',
            \Railroad\Railnotifications\Controllers\BroadcastNotificationJsonController::class . '@showNotificationBroadcast'
        )
            ->name('notification.broadcast.show');

        //User Notification Settings
        Route::get(
            '/user-notification-settings',
            \Railroad\Railnotifications\Controllers\UserNotificationSettingsJsonController::class . '@index'
        )
            ->name('user-notification-settings.index');

        Route::put(
            '/user-notification-settings',
            \Railroad\Railnotifications\Controllers\UserNotificationSettingsJsonController::class . '@store'
        )
            ->name('user-notification-settings.store');

        Route::patch(
            '/user-notification-settings',
            \Railroad\Railnotifications\Controllers\UserNotificationSettingsJsonController::class . '@update'
        )
            ->name('user-notification-settings.update');

        Route::delete(
            '/user-notification-settings',
            \Railroad\Railnotifications\Controllers\UserNotificationSettingsJsonController::class . '@delete'
        )
            ->name('user-notification-settings.delete');

        Route::patch(
            '/user-notification-settings/update',
            \Railroad\Railnotifications\Controllers\UserNotificationSettingsController::class . '@createOrUpdateUserNotificationsSettings'
        )
            ->name('user-notification-settings.update');

    }
);

