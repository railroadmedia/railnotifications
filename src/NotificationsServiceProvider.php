<?php

namespace Railroad\Railnotifications;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Common\EventManager;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Gedmo\DoctrineExtensions;
use Gedmo\Sortable\SortableListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Railroad\Doctrine\TimestampableListener;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeTimezoneType;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeType;
use Railroad\Doctrine\Types\Carbon\CarbonDateType;
use Railroad\Doctrine\Types\Carbon\CarbonTimeType;
use Railroad\Railnotifications\Commands\SetUserNotificationSettings;
use Railroad\Railnotifications\Events\NotificationBroadcast;
use Railroad\Railnotifications\Listeners\NotificationEventListener;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;
use Railroad\Railnotifications\Types\UserType;
use Redis;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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
        $this->listen = [
            NotificationBroadcast::class => [NotificationEventListener::class . '@handle'],
        ];

        parent::boot();

        // migrations: only run migrations if this is the master 'host' implementation
        if (config('railnotifications.data_mode') == 'host') {
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }

        $this->publishes([
                __DIR__ . '/../config/railnotifications.php' => config_path('railnotifications.php'),
            ]);

        //load package routes file
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // views
        $this->loadViewsFrom(__DIR__ . '/../views', 'railnotifications');

        //artisan commands
        $this->commands([
            SetUserNotificationSettings::class,
        ]);
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    public function register()
    {
        Type::overrideType('datetime', CarbonDateTimeType::class);
        Type::overrideType('datetimetz', CarbonDateTimeTimezoneType::class);
        Type::overrideType('date', CarbonDateType::class);
        Type::overrideType('time', CarbonTimeType::class);

        !Type::hasType(UserType::USER_TYPE) ? Type::addType(UserType::USER_TYPE, UserType::class) : null;

        // set proxy dir to temp folder on server
        $proxyDir = sys_get_temp_dir();

        // array cache
        $arrayCacheAdapter = new ArrayAdapter();
        $doctrineArrayCache = DoctrineProvider::wrap($arrayCacheAdapter);
        app()->instance('EcommerceArrayCache', $doctrineArrayCache);

        // file cache
        $phpFileCacheAdapter = new FilesystemAdapter('', 0, $proxyDir);
        $doctrineFileCache = DoctrineProvider::wrap($arrayCacheAdapter);

        // annotation reader
        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new IndexedReader(new AnnotationReader());

        $cachedAnnotationReader = new PsrCachedReader(
            $annotationReader,
            $phpFileCacheAdapter,
            env('APP_DEBUG', false)
        );

        $driverChain = new MappingDriverChain();

        DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
            $driverChain,
            $cachedAnnotationReader
        );

        foreach (config('railnotifications.entities') as $driverConfig) {
            $annotationDriver = new AnnotationDriver(
                $cachedAnnotationReader, $driverConfig['path']
            );

            $driverChain->addDriver(
                $annotationDriver,
                $driverConfig['namespace']
            );
        }

        // driver chain instance is referenced in laravel container to be reused when needed
        $timestampableListener = new TimestampableListener();
        $timestampableListener->setAnnotationReader($cachedAnnotationReader);

        $sortableListener = new SortableListener();
        $sortableListener->setAnnotationReader($cachedAnnotationReader);

        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($timestampableListener);
        $eventManager->addEventSubscriber($sortableListener);

        $ormConfiguration = new Configuration();

        $ormConfiguration->setMetadataCache($phpFileCacheAdapter);
        $ormConfiguration->setQueryCache($phpFileCacheAdapter);
        $ormConfiguration->setResultCache($arrayCacheAdapter);
        $ormConfiguration->setProxyDir($proxyDir);
        $ormConfiguration->setProxyNamespace('DoctrineProxies');
        $ormConfiguration->setAutoGenerateProxyClasses(
            config('railnotifications.development_mode') ? AbstractProxyFactory::AUTOGENERATE_ALWAYS :
                AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS
        );
        $ormConfiguration->setMetadataDriverImpl($driverChain);
        $ormConfiguration->setNamingStrategy(
            new UnderscoreNamingStrategy(CASE_LOWER)
        );

        // orm configuration instance is referenced in laravel container to be reused when needed
        if (config('railnotifications.database_in_memory') !== true) {
            $databaseOptions = [
                'driver' => config('railnotifications.database_driver'),
                'dbname' => config('railnotifications.database_name'),
                'user' => config('railnotifications.database_user'),
                'password' => config('railnotifications.database_password'),
                'host' => config('railnotifications.database_host'),
            ];
        } else {
            $databaseOptions = [
                'driver' => config('railnotifications.database_driver'),
                'user' => config('railnotifications.database_user'),
                'password' => config('railnotifications.database_password'),
                'memory' => true,
            ];
        }

        //register the entity manager
        $entityManager = RailnotificationsEntityManager::create(
            $databaseOptions,
            $ormConfiguration,
            $eventManager
        );

        // register the entity manager as a singleton
        app()->instance(RailnotificationsEntityManager::class, $entityManager);
    }
}
