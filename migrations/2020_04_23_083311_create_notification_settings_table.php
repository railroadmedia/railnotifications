<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::connection(config('railnotifications.database_connection_name'))->create(
            'notification_settings',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->index();
                $table->string('setting_name')->index();
                $table->string('setting_value')->index();
                $table->timestamps();

                $table->index(['user_id', 'setting_name'], 'notification_settings_usn');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('railnotifications.database_connection_name'))->dropIfExists('notification_settings');
    }
}
