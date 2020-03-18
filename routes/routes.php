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
            '/read/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@markAsRead'
        )
            ->name('notification.read');

        Route::put(
            '/read-all/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@update'
        )
            ->name('notification.update');

        Route::delete(
            '/notification/{id}',
            \Railroad\Railnotifications\Controllers\NotificationJsonController::class . '@delete'
        )
            ->name('notification.delete');
    }
);

