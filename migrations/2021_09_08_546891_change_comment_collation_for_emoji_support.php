<?php

use Illuminate\Database\Migrations\Migration;
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
        if (config('railnotifications.database_in_memory') !== true) {
            Schema::connection(config('railnotifications.database_connection_name'))->table(
                'notifications',
                function ($table) {
                    $table->string('comment')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
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
        if (config('railnotifications.database_in_memory') !== true) {
            Schema::connection(config('railnotifications.database_connection_name'))->table(
                'notifications',
                function ($table) {
                    $table->string('comment')->charset('utf8')->collation('utf8_unicode_ci')->change();
                }
            );
        }
    }
}
