<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_type_id')->length(10)->nullable();
            $table->string('bea_mo_reason',100)->nullable();
            $table->string('bea_so_reason',100)->nullable();
            $table->string('pullout_reason',100)->nullable();
            $table->string('status',10)->default('ACTIVE')->nullable();
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
        Schema::dropIfExists('reasons');
    }
}
