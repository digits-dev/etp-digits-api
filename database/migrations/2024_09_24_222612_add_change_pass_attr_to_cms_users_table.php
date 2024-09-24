<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangePassAttrToCmsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->dateTime('last_password_updated_at')->nullable()->after('status');
            $table->tinyInteger('waive_count')->default(0)->nullable()->after('last_password_updated_at');
            $table->tinyInteger('has_seen_notif')->default(0)->nullable()->after('waive_count');
            $table->tinyInteger('has_seen_changelog')->default(0)->nullable()->after('has_seen_notif');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->dropColumn('last_password_updated_at');
            $table->dropColumn('waive_count');
            $table->dropColumn('has_seen_notif');
            $table->dropColumn('has_seen_changelog');
        });
    }
}
