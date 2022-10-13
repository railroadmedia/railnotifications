<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ChangeNotificationsTextColumnsToMedium extends Migration
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
                function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->mediumText('data')->comment(' ')->change();
                    $table->mediumText('comment')->comment(' ')->change();
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
                    $table->text('data')->comment('')->change();
                    $table->text('comment')->comment('')->change();
                }
            );
        }
    }
}
