<?php

namespace Railroad\Railnotifications\Tests;

use Carbon\Carbon;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Exception;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpociot\ApiDoc\ApiDocGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PDO;
use PDOException;
use PHPUnit\Framework\ExpectationFailedException;
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
use SebastianBergmann\Comparator\ComparisonFailure;
use SQLite3;

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

    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldReceive('id')->andReturn(1);

        // Run the schema update tool using our entity metadata
        $this->entityManager = app(RailnotificationsEntityManager::class);

        $userProvider = new UserProvider();

        $this->app->instance(UserProviderInterface::class, $userProvider);
        $this->app->instance(DoctrineArrayHydratorUserProviderInterface::class, $userProvider);
        $this->app->instance(DoctrineUserProviderInterface::class, $userProvider);

        $contentProvider = new ContentProvider();
        $this->app->instance(ContentProviderInterface::class, $contentProvider);

        $railforumProvider = new ForumProvider();
        $this->app->instance(RailforumProviderInterface::class, $railforumProvider);

        $host = env('MYSQL_HOST', 'mysql8');
        $port = env('MYSQL_PORT', '3306');
        $username = env('MYSQL_USER_NAME', 'root');
        $password = env('MYSQL_PASSWORD', 'root');
        $database = env('MYSQL_DATABASE_NAME', 'railnotifications_automated_tests');

        try {
            $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create the database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        } catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }

        $this->artisan('migrate:fresh', []);
        $this->artisan('cache:clear', []);

        $this->faker = Factory::create();

        $this->databaseManager = $this->app->make(DatabaseManager::class);

        Carbon::setTestNow(Carbon::now());

        $this->createUsersTable();
    }

    protected function tearDown(): void
    {
        $this->databaseManager->connection()->statement('DROP DATABASE IF EXISTS ' . env('MYSQL_DATABASE_NAME', 'railnotifications_automated_tests'));

        parent::tearDown();
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

        // Setup MySQL database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'mysql',
            'host' => env('MYSQL_HOST', 'mysql8'),
            'port' => env('MYSQL_PORT', '3306'),
            'database' => env('MYSQL_DATABASE_NAME', 'railnotifications_automated_tests'),
            'username' => env('MYSQL_USER_NAME', 'root'),
            'password' => env('MYSQL_PASSWORD', 'root'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        $app['config']->set('railnotifications.redis_host', $defaultConfig['redis_host']);
        $app['config']->set('railnotifications.redis_port', $defaultConfig['redis_port']);

        $app['config']->set(
            'railnotifications.entities',
            array_merge(
                $defaultConfig['entities']
            )
        );

        $app['config']->set('railnotifications.database_driver', 'pdo_mysql');
        $app['config']->set('railnotifications.database_host', env('MYSQL_HOST', 'mysql8'));
        $app['config']->set('railnotifications.database_port', env('MYSQL_PORT', '3306'));
        $app['config']->set('railnotifications.database_name', env('MYSQL_DATABASE_NAME', 'railnotifications_automated_tests'));
        $app['config']->set('railnotifications.database_user', env('MYSQL_USER_NAME', 'root'));
        $app['config']->set('railnotifications.database_password', env('MYSQL_PASSWORD', 'root'));
        $app['config']->set('railnotifications.database_in_memory', false);
        $app['config']->set('railnotifications.development_mode', true);
        $app['config']->set('railnotifications.data_mode', 'host');

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

        $app['config']->set('railnotifications.mapping_types', $defaultConfig['mapping_types']);
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
// dd(Auth::shouldReceive('id'));
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

    protected function assertArraySubset(array $subset, array $array, bool $strict = false, string $message = '')
    {
        $differences = [];

        $findDifferences = function ($subset, $array, $path = '') use (&$findDifferences, $strict, &$differences) {
            foreach ($subset as $key => $value) {
                $currentPath = $path ? "{$path}.{$key}" : $key;

                if (!array_key_exists($key, $array)) {
                    $differences[] = ["path" => $currentPath, "expected" => $value, "actual" => "<<missing>>"];
                continue;
            }

                if (is_array($value)) {
                    if (!is_array($array[$key])) {
                        $differences[] = ["path" => $currentPath, "expected" => "array", "actual" => gettype($array[$key])];
                    } else {
                        $findDifferences($value, $array[$key], $currentPath);
                    }
                } else {
                    $match = $strict ? $array[$key] === $value : $array[$key] == $value;
                    if (!$match) {
                        $differences[] = [
                            "path" => $currentPath,
                            "expected" => $value,
                            "actual" => $array[$key]
                        ];
                    }
                }
            }
        };

        $findDifferences($subset, $array);

        $formatValue = function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            if (is_null($value)) {
                return 'null';
            }
            if (is_string($value)) {
                return "'{$value}'";
            }
            if (is_array($value)) {
                return 'array(' . count($value) . ')';
            }
            return var_export($value, true);
        };

        if (!empty($differences)) {
            $context = $strict ? 'strict' : 'non-strict';
            $failureDescription = sprintf(
                "Failed asserting that an array has the subset.\nDifferences found (%s mode):\n%s",
                $context,
                implode("\n", array_map(function ($diff) use ($formatValue) {
                    return sprintf(
                        "  At path '%s':\n    Expected: %s\n    Actual: %s",
                        $diff['path'],
                        $formatValue($diff['expected']),
                        $formatValue($diff['actual'])
                    );
                }, $differences))
            );

            throw new ExpectationFailedException(
                $message . "\n" . $failureDescription,
                new ComparisonFailure($subset, $array, var_export($subset, true), var_export($array, true))
            );
        }

        $this->assertEmpty($differences);
    }
}