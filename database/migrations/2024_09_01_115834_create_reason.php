<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reason', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_type_id')->length(10)->nullable();
            $table->string('bea_mo_reason')->length(255)->nullable();
            $table->string('bea_so_reason')->length(30)->nullable();
            $table->string('pullout_reason')->length(255)->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('allow_multi_items')->length(10)->nullable();
            $table->unsignedInteger('created_by')->length(10)->nullable();
            $table->unsignedInteger('updated_by')->length(10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reason');
    }
}
