<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelToStoreMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_masters', function (Blueprint $table) {
            $table->unsignedInteger('channels_id')->nullable()->after('org_subinventory');
            // $table->foreign('channels_id')->references('id')->on('channels');
            $table->unsignedInteger('to_org_id')->nullable()->after('channels_id');
            $table->string('store_type',5)->nullable()->after('warehouse_type');

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
            $table->dropColumn('channels_id');
            $table->dropColumn('to_org_id');
            $table->dropColumn('store_type');
        });
    }
}
