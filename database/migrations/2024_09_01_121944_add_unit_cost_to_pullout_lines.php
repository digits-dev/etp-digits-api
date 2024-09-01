<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitCostToPulloutLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pullout_lines', function (Blueprint $table) {
            $table->decimal('unit_price', 16, 2)->default(0)->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pullout_lines', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
}
