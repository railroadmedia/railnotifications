<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddAuthorAndContentToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('railnotifications.database_connection_name'))->table(
            'notifications',
            function ($table) {
                /**
                 * @var $table \Illuminate\Database\Schema\Blueprint
                 */
                $table->string('author_id', 255)->after('recipient_id')->nullable()->index();
                $table->string('author_avatar', 255)->after('recipient_id')->nullable()->index();
                $table->string('author_display_name', 255)->after('author_avatar')->nullable()->index();
                $table->string('content_title', 255)->after('author_display_name')->nullable()->index();
                $table->string('content_url', 255)->after('content_title')->nullable()->index();
                $table->string('content_mobile_app_url', 255)->after('content_url')->nullable()->index();
                $table->text('comment', 255)->after('content_mobile_app_url')->nullable();
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
        Schema::connection(config('railnotifications.database_connection_name'))->table(
            ConfigService::$tableContent,
            function ($table) {
                /**
                 * @var $table \Illuminate\Database\Schema\Blueprint
                 */

                $table->dropColumn('author_id');
                $table->dropColumn('author_avatar');
                $table->dropColumn('author_display_name');
                $table->dropColumn('content_title');
                $table->dropColumn('content_url');
                $table->dropColumn('content_mobile_app_url');
                $table->dropColumn('comment');
            }
        );
    }
}
