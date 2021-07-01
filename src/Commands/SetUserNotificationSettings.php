<?php

namespace Railroad\Railnotifications\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

class SetUserNotificationSettings extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'SetUserNotificationSettings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user notifications settings for all the brands';

    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * SyncUserNotificationSettings constructor.
     *
     * @param DatabaseManager $databaseManager
     */
    public function __construct(
        DatabaseManager $databaseManager
    )
    {
        parent::__construct();

        $this->databaseManager = $databaseManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Syncing User Notification Settings.');

        $settingsNames = [
            'notify_on_lesson_comment_reply',
            'notify_on_lesson_comment_like',
            'notify_on_forum_followed_thread_reply',
            'notify_on_forum_post_like',
        ];

        foreach ($settingsNames as $settingName) {
            $this->syncSettings($settingName);
        }

        $this->info('Migration completed. ');
    }

    /**
     * @param string $settingName
     * @return string|void
     */
    private function syncSettings(string $settingName)
    {
        $this->databaseManager->connection('musora_mysql')
            ->table('forum_categories')
            ->truncate();

        $brands = ['pianote', 'drumeo', 'guitareo', 'singeo'];
        $sql = <<<'EOT'
INSERT INTO %s (
    `user_id`,
    `setting_name`,
    `setting_value`,
        `brand`,
    `created_at`
)
SELECT
    u.`id` AS `user_id`,
    '%s' AS `setting_name`,
    u.`%s` AS `setting_value`,
   '%s' AS `brand`,
    u.`created_at` AS `created_at`
FROM `%s` u
EOT;

        foreach ($brands as $brand) {
            $statement = sprintf(
                $sql,
                'notification_settings',
                $settingName,
                $settingName,
                $brand,
                'usora_users'
            );

            $this->databaseManager->connection('musora_mysql')
                ->statement($statement);
        }

        return $statement;
    }

}