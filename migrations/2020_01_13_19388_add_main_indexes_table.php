<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMainIndexesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'notifications',
            function (Blueprint $table) {
                $table->index('subject_id');
                $table->index('recipient_id');
                $table->index('read_on');
                $table->index('created_on');
                $table->index('type');
                $table->index('created_at');
                $table->index('updated_at');
            }
        );

        Schema::table(
            'notification_broadcasts',
            function (Blueprint $table) {
                $table->index('channel');
                $table->index('type');
                $table->index('status');
                $table->index('notification_id');
                $table->index('aggregation_group_id');
                $table->index('broadcast_on');
                $table->index('created_at');
                $table->index('updated_at');
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
        Schema::table(
            'notifications',
            function (Blueprint $table) {
                $table->dropIndex('subject_id');
                $table->dropIndex('recipient_id');
                $table->dropIndex('read_on');
                $table->dropIndex('created_on');
                $table->dropIndex('type');
                $table->dropIndex('created_at');
                $table->dropIndex('updated_at');
            }
        );

        Schema::table(
            'notification_broadcasts',
            function (Blueprint $table) {
                $table->dropIndex('channel');
                $table->dropIndex('type');
                $table->dropIndex('status');
                $table->dropIndex('notification_id');
                $table->dropIndex('aggregation_group_id');
                $table->dropIndex('broadcast_on');
                $table->dropIndex('created_at');
                $table->dropIndex('updated_at');
            }
        );
    }
}
