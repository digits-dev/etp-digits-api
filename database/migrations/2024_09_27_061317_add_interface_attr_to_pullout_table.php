<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInterfaceAttrToPulloutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pullouts', function (Blueprint $table) {
            $table->string('to_org_id',10)->nullable()->after('reasons_id');
            $table->date('transaction_date')->nullable()->after('status');
            $table->tinyInteger('interface_flag')->default(0)->after('transaction_date');
        });

        Schema::table('pullout_lines', function (Blueprint $table) {
            $table->tinyInteger('interface_flag')->default(0)->after('problem_details');
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
            $table->dropColumn('to_org_id');
            $table->dropColumn('transaction_date');
            $table->dropColumn('interface_flag');
        });

        Schema::table('pullout_lines', function (Blueprint $table) {
            $table->dropColumn('interface_flag');
        });
    }
}
