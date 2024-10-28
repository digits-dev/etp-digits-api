<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsSerialNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->renameColumn('store_pullout_lines', 'store_pullout_lines_id');
            $table->renameColumn('store_transfer_lines', 'store_transfer_lines_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->renameColumn('store_pullout_lines_id', 'store_pullout_lines');
            $table->renameColumn('store_transfer_lines_id', 'store_transfer_lines');
        });
    }
}
