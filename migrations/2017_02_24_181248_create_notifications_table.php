<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('railnotifications.database_connection_name'))->create(
            'notifications',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->text('data');
                $table->integer('subject_id')->nullable();
                $table->integer('recipient_id')->nullable();
                $table->dateTime('read_on')->nullable();
                $table->dateTime('created_on')->nullable();
                $table->timestamps();
            }
        );
//        var_dump("OID " . spl_object_id(DB::connection(config('railnotifications.database_connection_name'))));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('railnotifications.database_connection_name'))->dropIfExists('notifications');
    }
}
