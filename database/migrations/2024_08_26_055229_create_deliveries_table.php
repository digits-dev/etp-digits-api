<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->string('dr_number')->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_po')->nullable();
            $table->string('shipping_instruction')->nullable();
            $table->string('transaction_type',5)->nullable();
            $table->unsignedInteger('stores_id')->length(10)->nullable();
            $table->unsignedInteger('locators_id')->length(10)->nullable();
            $table->unsignedTinyInteger('is_trade')->default(1);
            $table->decimal('total_amount')->nullable();
            $table->unsignedInteger('total_qty')->length(10)->nullable();
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
        Schema::dropIfExists('deliveries');
    }
}
