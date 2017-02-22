<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'notifications',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->text('data');
                $table->integer('recipient_id')->nullable();
                $table->dateTime('read_on')->nullable();
                $table->dateTime('created_on')->nullable();
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
        Schema::dropIfExists('notifications');
    }
}
