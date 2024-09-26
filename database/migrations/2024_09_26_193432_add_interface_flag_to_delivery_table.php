<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInterfaceFlagToDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->tinyInteger('interface_flag')->default(0)->after('transaction_date');
        });

        Schema::table('delivery_lines', function (Blueprint $table) {
            $table->tinyInteger('interface_flag')->default(0)->after('transaction_date');
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
            $table->dropColumn('interface_flag');
        });

        Schema::table('delivery_lines', function (Blueprint $table) {
            $table->dropColumn('interface_flag');
        });
    }
}
