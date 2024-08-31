<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePulloutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pullouts', function (Blueprint $table) {
            $table->id();
            $table->string('sor_mor_number',50)->nullable();
            $table->string('document_number',50)->nullable();
            $table->string('memo')->nullable();
            $table->date('picklist_date')->nullable();
            $table->date('pickconfirm_date')->nullable();
            $table->string('transaction_type',10)->nullable();
            $table->string('wh_from')->nullable();
            $table->string('wh_to')->nullable();
            $table->unsignedInteger('reasons_id')->length(10)->nullable();
            $table->unsignedInteger('channels_id')->length(10)->nullable();
            $table->unsignedInteger('stores_id')->length(10)->nullable();
            $table->unsignedInteger('status')->default(0)->comment('0-pending,1-approved,2-processing,3-received')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pullouts');
    }
}
