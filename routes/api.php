<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => 'api/railnotifications',
        'middleware' => config('railnotifications.api_middleware'),
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
            '/read-all/{id}',
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
    }
);

