<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeploymentFlagToStoreMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_masters', function (Blueprint $table) {
            $table->unsignedSmallInteger('is_deployed')->default(0)->after('status');
            $table->dateTime('is_deployed_at')->nullable()->after('is_deployed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_masters', function (Blueprint $table) {
            $table->dropColumn('is_deployed');
            $table->dropColumn('is_deployed_at');
        });
    }
}
