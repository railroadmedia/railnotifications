<?php

namespace Railroad\Railnotifications;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Gedmo\DoctrineExtensions;
use Gedmo\Sortable\SortableListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Railroad\Doctrine\TimestampableListener;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeTimezoneType;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeType;
use Railroad\Doctrine\Types\Carbon\CarbonDateType;
use Railroad\Doctrine\Types\Carbon\CarbonTimeType;
use Railroad\Ecommerce\Drivers\ExistingPDOSqliteDriver;
use Railroad\Railnotifications\Commands\SetUserNotificationSettings;
use Railroad\Railnotifications\Events\NotificationBroadcast;
use Railroad\Railnotifications\Listeners\NotificationEventListener;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;
use Railroad\Railnotifications\Types\UserType;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NotificationsServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    public function register()
    {
        Type::overrideType('datetime', CarbonDateTimeType::class);
        Type::overrideType('datetimetz', CarbonDateTimeTimezoneType::class);
        Type::overrideType('date', CarbonDateType::class);
        Type::overrideType('time', CarbonTimeType::class);

        !Type::hasType(UserType::USER_TYPE) ? Type::addType(UserType::USER_TYPE, UserType::class) : null;

        $proxyDir = sys_get_temp_dir();

        $arrayCacheAdapter = new ArrayAdapter();
        $phpFileCacheAdapter = new FilesystemAdapter('', 0, $proxyDir);

        $annotationReader = new AnnotationReader();
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
        $ormConfiguration->setResultCache($phpFileCacheAdapter);
        $ormConfiguration->setProxyDir($proxyDir);
        $ormConfiguration->setProxyNamespace('DoctrineProxies');
        $ormConfiguration->setAutoGenerateProxyClasses(
            config('railnotifications.development_mode')
        );
        $ormConfiguration->setMetadataDriverImpl($driverChain);
        $ormConfiguration->setNamingStrategy(
            new UnderscoreNamingStrategy(CASE_LOWER)
        );

        $databaseOptions = [
            'driver' => config('railnotifications.database_driver'),
            'dbname' => config('railnotifications.database_name'),
            'user' => config('railnotifications.database_user'),
            'password' => config('railnotifications.database_password'),
            'host' => config('railnotifications.database_host'),
        ];

        $connection = DriverManager::getConnection($databaseOptions, $ormConfiguration);

        $entityManager = new RailnotificationsEntityManager($connection, $ormConfiguration, $eventManager);

        app()->instance(RailnotificationsEntityManager::class, $entityManager);
    }
}