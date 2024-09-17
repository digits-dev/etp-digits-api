<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPulloutDetailsToPulloutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pullouts', function (Blueprint $table) {
            $table->date('received_date')->nullable()->after('stores_id');
            $table->decimal('total_amount')->nullable()->after('received_date');
            $table->unsignedInteger('total_qty')->length(10)->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pullouts', function (Blueprint $table) {
            $table->dropColumn('received_date');
            $table->dropColumn('total_amount');
            $table->dropColumn('total_qty');
        });
    }
}
