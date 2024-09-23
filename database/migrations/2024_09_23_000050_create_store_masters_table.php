<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_masters', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_code',10)->unique()->nullable();
            $table->unsignedTinyInteger('warehouse_type')->default(0)->nullable();
            $table->string('store_name',150)->nullable();
            $table->string('bea_so_store_name',250)->nullable();
            $table->string('bea_mo_store_name',250)->nullable();
            $table->string('doo_subinventory',50)->nullable();
            $table->string('sit_subinventory',50)->nullable();
            $table->string('org_subinventory',50)->nullable();
            $table->string('status',10)->default('ACTIVE')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
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
        Schema::dropIfExists('store_masters');
    }
}
