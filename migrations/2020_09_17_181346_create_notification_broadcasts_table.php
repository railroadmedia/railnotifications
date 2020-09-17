<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationBroadcastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('railnotifications.database_connection_name'))->create(
            'notification_broadcasts',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('channel', 1500);
                $table->string('type');
                $table->string('status');
                $table->text('report')->nullable();
                $table->integer('notification_id');
                $table->string('aggregation_group_id')->nullable();
                $table->dateTime('broadcast_on')->nullable();
                $table->timestamps();
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
        Schema::connection(config('railnotifications.database_connection_name'))->dropIfExists('notification_broadcasts');
    }
}
