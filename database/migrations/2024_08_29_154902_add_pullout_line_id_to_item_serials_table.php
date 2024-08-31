<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPulloutLineIdToItemSerialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_serials', function (Blueprint $table) {
            $table->foreignId('pullout_lines_id')->constrained()->onDelete('cascade')->after('delivery_lines_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_serials', function (Blueprint $table) {
            $table->dropColumn('pullout_lines_id');
        });
    }
}
