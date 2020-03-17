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

        Route::patch(
            '/notification/{id}',
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

