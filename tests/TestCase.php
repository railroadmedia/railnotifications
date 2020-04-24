<?php

namespace Railroad\Railnotifications\Tests;

use Carbon\Carbon;
use Doctrine\DBAL\Types\Type;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpociot\ApiDoc\ApiDocGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Doctrine\Providers\DoctrineServiceProvider;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeTimezoneType;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeType;
use Railroad\Doctrine\Types\Carbon\CarbonDateType;
use Railroad\Doctrine\Types\Carbon\CarbonTimeType;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Faker\Factory;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;
use Railroad\Railnotifications\NotificationsServiceProvider;
use Railroad\Railnotifications\Tests\Fixtures\ContentProvider;
use Railroad\Railnotifications\Tests\Fixtures\ForumProvider;
use Railroad\Railnotifications\Tests\Fixtures\UserProvider;
use Railroad\Railnotifications\Entities\User;

class TestCase extends BaseTestCase
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected function setUp()
    {
        parent::setUp();

        // Run the schema update tool using our entity metadata
        $this->entityManager = app(RailnotificationsEntityManager::class);

        $this->entityManager->getMetadataFactory()
            ->getCacheDriver()
            ->deleteAll();

        // make sure laravel is using the same connection
        DB::connection()
            ->setPdo(
                $this->entityManager->getConnection()
                    ->getWrappedConnection()
            );
        DB::connection()
            ->setReadPdo(
                $this->entityManager->getConnection()
                    ->getWrappedConnection()
            );

        $userProvider = new UserProvider();

        $this->app->instance(UserProviderInterface::class, $userProvider);
        $this->app->instance(DoctrineArrayHydratorUserProviderInterface::class, $userProvider);
        $this->app->instance(DoctrineUserProviderInterface::class, $userProvider);

        $contentProvider = new ContentProvider();
        $this->app->instance(ContentProviderInterface::class, $contentProvider);

        $railforumProvider = new ForumProvider();
        $this->app->instance(RailforumProviderInterface::class, $railforumProvider);

        $this->artisan('migrate', []);

        $this->faker = Factory::create();

        $this->databaseManager = $this->app->make(DatabaseManager::class);

        Carbon::setTestNow(Carbon::now());

        $this->createUsersTable();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $defaultConfig = require(__DIR__ . '/../config/railnotifications.php');

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );
        $app['config']->set('railnotifications.redis_host', $defaultConfig['redis_host']);
        $app['config']->set('railnotifications.redis_port', $defaultConfig['redis_port']);

        $app['config']->set(
            'railnotifications.entities',
            array_merge(
                $defaultConfig['entities']
            )
        );

        $app['config']->set('railnotifications.database_driver', 'pdo_sqlite');
        $app['config']->set('railnotifications.database_user', 'root');
        $app['config']->set('railnotifications.database_password', 'root');
        $app['config']->set('railnotifications.database_in_memory', true);
        $app['config']->set('railnotifications.development_mode', true);

        $app['config']->set('railnotifications.brand', $defaultConfig['brand']);

        $app->register(NotificationsServiceProvider::class);

        $app['config']->set('doctrine.redis_host', $defaultConfig['redis_host']);
        $app['config']->set('doctrine.redis_port', $defaultConfig['redis_port']);

        $app['config']->set(
            'doctrine.entities',
            array_merge(
                $defaultConfig['entities']
            )
        );

        $app['config']->set('doctrine.database_driver', 'pdo_sqlite');
        $app['config']->set('doctrine.database_user', 'root');
        $app['config']->set('doctrine.database_password', 'root');
        $app['config']->set('doctrine.database_in_memory', true);
        $app['config']->set('doctrine.development_mode', true);
        $app->register(DoctrineServiceProvider::class);

        // allows access to built in user auth
        $app['config']->set('auth.providers.users.model', User::class);

        $app->bind(
            'UserProviderInterface',
            function () {
                $mock =
                    $this->getMockBuilder('UserProviderInterface')
                        ->setMethods(['create'])
                        ->getMock();

                $mock->method('create')
                    ->willReturn(
                        [
                            'id' => 1,
                            'email' => $this->faker->email,
                        ]
                    );
                return $mock;
            }
        );

        $app->bind(
            'ContentProviderInterface',
            function () {
                $mock =
                    $this->getMockBuilder('ContentProviderInterface')
                        ->setMethods(['getContentById'])
                        ->getMock();

                $mock->method('getContentById')
                    ->willReturn(
                        [
                            'id' => 1,
                            'email' => $this->faker->email,
                        ]
                    );
                return $mock;
            }
        );


        //apidoc
        $apiDocConfig = require(__DIR__ . '/../config/apidoc.php');

        $app['config']->set('apidoc.output', $apiDocConfig['output']);
        $app['config']->set('apidoc.routes', $apiDocConfig['routes']);
        $app['config']->set('apidoc.example_languages', $apiDocConfig['example_languages']);
        $app['config']->set('apidoc.fractal', $apiDocConfig['fractal']);
        $app['config']->set('apidoc.requiredEntities', $apiDocConfig['requiredEntities']);
        $app['config']->set('apidoc.entityManager', $apiDocConfig['entityManager']);
        $app['config']->set('apidoc.postman', $apiDocConfig['postman']);
        $app->register(ApiDocGeneratorServiceProvider::class);
    }

    /**
     * We don't want to use mockery so this is a reimplementation of the mockery version.
     *
     * @param  array|string $events
     * @return $this
     *
     * @throws Exception
     */
    public function expectsEvents($events)
    {
        $events = is_array($events) ? $events : func_get_args();

        $mock =
            $this->getMockBuilder(Dispatcher::class)
                ->setMethods(['fire', 'dispatch'])
                ->getMockForAbstractClass();

        $mock->method('fire')
            ->willReturnCallback(
                function ($called) {
                    $this->firedEvents[] = $called;
                }
            );

        $mock->method('dispatch')
            ->willReturnCallback(
                function ($called) {
                    $this->firedEvents[] = $called;
                }
            );

        $this->app->instance('events', $mock);

        $this->beforeApplicationDestroyed(
            function () use ($events) {
                $fired = $this->getFiredEvents($events);
                if ($eventsNotFired = array_diff($events, $fired)) {
                    throw new Exception(
                        'These expected events were not fired: [' . implode(', ', $eventsNotFired) . ']'
                    );
                }
            }
        );

        return $this;
    }

    protected function createUsersTable()
    {
        if (!app('db')->connection()
            ->getSchemaBuilder()
            ->hasTable('users')) {
            app('db')->connection()
                ->getSchemaBuilder()
                ->create(
                    'users',
                    function (Blueprint $table) {
                        $table->increments('id');
                        $table->string('email')->nullable();
                        $table->string('password')->nullable();
                        $table->string('display_name')->nullable();
                        $table->string('profile_picture_url')->nullable();
                        $table->string('firebase_token_web')->nullable();
                        $table->string('firebase_token_ios')->nullable();
                        $table->string('firebase_token_android')->nullable();
                        $table->timestamps();
                    }
                );
        }
    }

    /**
     * @return int
     */
    public function createAndLogInNewUser($email = null)
    {
        if (!$email) {
            $email = $this->faker->email;
        }

        $userId =
            $this->databaseManager->table('users')
                ->insertGetId(
                    [
                        'email' => $email,
                        'password' => $this->faker->password,
                        'display_name' => $this->faker->name,
                        'profile_picture_url' => $this->faker->url,
                        'created_at' => Carbon::now()
                            ->toDateTimeString(),
                        'updated_at' => Carbon::now()
                            ->toDateTimeString(),
                    ]
                );
dd(Auth::shouldReceive('id'));
//        Auth::shouldReceive('check')
//            ->andReturn(true);
//
//        Auth::shouldReceive('id')
//            ->andReturn($userId);
//
//        $userMockResults = ['id' => $userId, 'email' => $email];
//        Auth::shouldReceive('user')
//            ->andReturn($userMockResults);

        return $userId;
    }

    /**
     * Helper method to seed a test user
     * this method does not log in the newly created user
     *
     * @return array
     */
    public function fakeUser($userData = [])
    {
        $userData += [
            'email' => $this->faker->email,
            'password' => $this->faker->password,
            'display_name' => $this->faker->name,
            'profile_picture_url' => $this->faker->url,
            'created_at' => Carbon::now()
                ->toDateTimeString(),
            'updated_at' => Carbon::now()
                ->toDateTimeString(),
        ];

        $userId =
            $this->databaseManager->table('users')
                ->insertGetId($userData);

        $userData['id'] = $userId;

        return $userData;
    }

    /**
     * Helper method to seed a test notification
     *
     * @return array
     */
    public function fakeNotification($notificationStub = []): array
    {
        $notification = $this->faker->notification($notificationStub);

        $notificationId =
            $this->databaseManager->table('notifications')
                ->insertGetId($notification);

        $notification['id'] = $notificationId;

        return $notification;
    }


    /**
     * Helper method to seed a test notification broadcast
     *
     * @return array
     */
    public function fakeNotificationBroadcast($notificationBroadcastStub = []): array
    {
        $notificationBroadcast = $this->faker->notificationBroadcast($notificationBroadcastStub);

        $notificationBroadcastId =
            $this->databaseManager->table('notification_broadcasts')
                ->insertGetId($notificationBroadcast);

        $notificationBroadcast['id'] = $notificationBroadcastId;

        return $notificationBroadcast;
    }

    /**
     * Helper method to seed a test user notification setting
     *
     * @return array
     */
    public function fakeUserNotificationSetting($notificationSettingStub = []): array
    {
        $userNotificationSetting = $this->faker->userNotificationSetting($notificationSettingStub);

        $notificationSettingId =
            $this->databaseManager->table('notification_settings')
                ->insertGetId($userNotificationSetting);

        $userNotificationSetting['id'] = $notificationSettingId;

        return $userNotificationSetting;
    }
}