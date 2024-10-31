<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferGroupToStoreMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_masters', function (Blueprint $table) {
            $table->unsignedInteger('transfer_groups_id')->nullable()->after('org_subinventory');
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
            $table->dropColumn('transfer_groups_id');
        });
    }
}
