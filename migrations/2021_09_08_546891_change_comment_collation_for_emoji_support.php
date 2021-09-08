<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class ChangeCommentCollationForEmojiSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config()->get('database.default') != 'testbench') {
            Schema::connection(config('railnotifications.database_connection_name'))->table(
                'notifications',
                    function ($table) {

                        DB::connection(config('railnotifications.database_connection_name'))
                            ->statement(
                                'ALTER TABLE notifications 
                                  MODIFY comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
                            );
                    }
                );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config()->get('database.default') != 'testbench') {
            Schema::connection(config('railnotifications.database_connection_name'))->table(
                'notifications',
                    function ($table) {

                        DB::connection(config('railnotifications.database_connection_name'))
                            ->statement(
                                'ALTER TABLE notifications 
                                  MODIFY comment TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci;'
                            );
                    }
                );
        }
    }
}
