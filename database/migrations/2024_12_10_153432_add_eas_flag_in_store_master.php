<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEasFlagInStoreMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_masters', function (Blueprint $table) {
            $table->tinyInteger('eas_flag')->default(0)->after('status')->comment('0-false,1-true');
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
            $table->dropColumn('eas_flag');
        });
    }
}
