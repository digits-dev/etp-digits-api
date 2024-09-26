<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrgAttrToDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('from_org_id',10)->nullable()->after('locators_id');
            $table->string('to_org_id',10)->nullable()->after('from_org_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('from_org_id');
            $table->dropColumn('to_org_id');
        });
    }
}
