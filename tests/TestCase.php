<?php

namespace Railroad\Railnotifications\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Faker\Factory;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;
use Railroad\Railnotifications\NotificationsServiceProvider;
use Railroad\Railnotifications\Tests\Fixtures\UserProvider;

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

        $app->register(NotificationsServiceProvider::class);

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
                        $table->string('email');
                        $table->string('password');
                        $table->string('display_name');
                        $table->string('profile_picture_url')->nullable();
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
     * Helper method to seed a test product
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
}