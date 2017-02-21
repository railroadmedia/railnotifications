<?php namespace Railroad\Railforums;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Railroad\Railforums\DataMappers\UserCloakDataMapper;
use Railroad\Railforums\EventListeners\EntityEventListener;
use Railroad\Railmap\Events\EntityDestroyed;
use Railroad\Railmap\Events\EntitySaved;

class NotificationsServiceProvider extends ServiceProvider
{
    protected $listen = [];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->publishes(
            [
                __DIR__ . '/../config/railnotifications.php' => config_path('railnotifications.php'),
            ]
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
