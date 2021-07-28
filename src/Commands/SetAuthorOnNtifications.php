<?php

namespace Railroad\Railnotifications\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Railroad\Railforums\Repositories\PostRepository;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;

class SetAuthorOnNtifications extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'SetAuthorOnNtifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SetAuthorOnNtifications';

    /**
     * @var DatabaseManager
     */
    private $databaseManager;
    /**
     * @var RailnotificationsEntityManager
     */
    private $entityManager;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository|\Doctrine\Persistence\ObjectRepository
     */
    private $notificationRepository;

    /**
     * SetAuthorOnNtifications constructor.
     * @param DatabaseManager $databaseManager
     * @param RailnotificationsEntityManager $entityManager
     * @param PostRepository $postRepository
     */
    public function __construct(
        DatabaseManager $databaseManager,
        RailnotificationsEntityManager $entityManager,
        PostRepository $postRepository
    )
    {
        parent::__construct();

        $this->databaseManager = $databaseManager;
        $this->postRepository = $postRepository;

        $this->entityManager = $entityManager;
        $this->notificationRepository = $this->entityManager->getRepository(Notification::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('SetAuthorOnNtifications');
        $this->info('Starting ' . Carbon::now()->toDateTimeString());
        $databaseManager = $this->databaseManager;
        $postRepo = $this->postRepository;

        $this->databaseManager->connection('musora_mysql')
            ->table('notifications')
            ->where('type', '=', Notification::TYPE_FORUM_POST_REPLY)
            ->orderBy('id', 'desc')
            ->chunkById(
                5000,
                function (Collection $notifications) use ($databaseManager, $postRepo) {

                    $notificationPosts = [];

                    foreach ($notifications as $notification) {
                        $data = json_decode($notification->data, true);

                        $id = $data['postId'];
                        $notificationPosts[$notification->id] = $id;
                    }

                    $authorIds = $postRepo->getPostsAuthorIds((array_values($notificationPosts)));

                    try {
                        $q = "UPDATE notifications SET subject_id = CASE ";

                        foreach ($notifications as $notification) {
                            if (array_key_exists($notificationPosts[$notification->id], $authorIds)) {
                                $q .= "WHEN id = " . $notification->id . " THEN '" . $authorIds[$notificationPosts[$notification->id]] . "' ";
                            }
                        }
                        $q .= "ELSE subject_id END ";
                        $databaseManager->connection('musora_mysql')->statement($q);
                    } catch (\Exception $e) {

                    }

                    $this->info('RAM usage: ' . round(memory_get_usage(true) / 1048576, 2));
                    $this->info('Completed 1000 authors for ' . Notification::TYPE_FORUM_POST_REPLY . ' current time' . Carbon::now()->toDateTimeString());
                });

        $i = 0;
        $this->databaseManager->connection('musora_mysql')
            ->table('notifications')
            ->select(['id', 'data'])
            ->where('type', '=', Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD)
            ->whereNull('subject_id')
            ->orderBy('id', 'desc')
            ->chunkById(
                500,
                function (Collection $notifications) use ($databaseManager, $postRepo, &$i) {

                    $notificationPosts = [];

                    foreach ($notifications as $notification) {
                        $data = json_decode($notification->data, true);

                        $id = $data['postId'];
                        $notificationPosts[$notification->id] = $id;

                        $i++;

                    }

                    $authorIds = $postRepo->getPostsAuthorIds((array_values($notificationPosts)));


                    try {
                        $q = "UPDATE notifications SET subject_id = CASE ";

                        foreach ($notifications as $notification) {
                            if (array_key_exists($notificationPosts[$notification->id], $authorIds)) {
                                $q .= "WHEN id = " . $notification->id . " THEN '" . $authorIds[$notificationPosts[$notification->id]] . "' ";
                            }
                        }
                        $q .= "ELSE subject_id END ";

                        if ($databaseManager->connection('musora_mysql')->statement($q)) {

                        } else {
                            // dd($q);
                        }
                    } catch (\Exception $e) {
//dd($e);
                    }

                    $this->info('RAM usage: ' . round(memory_get_usage(true) / 1048576, 2));
                    //$this->info('Completed 500 authors for '.Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD. ' current time ' . Carbon::now()->toDateTimeString());
                }
            );
        $this->info('Forum post in followed thread ->' . $i);

        $this->info('Migration completed. ' . Carbon::now()->toDateTimeString());
    }
}