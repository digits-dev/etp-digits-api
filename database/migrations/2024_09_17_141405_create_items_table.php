<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('beach_item_id')->nullable();
            $table->string('digits_code',100)->unique()->nullable();
            $table->string('upc_code',200)->nullable();
            $table->string('upc_code2',200)->nullable();
            $table->string('upc_code3',200)->nullable();
            $table->string('upc_code4',200)->nullable();
            $table->string('upc_code5',200)->nullable();
            $table->string('item_description',250)->nullable();
            $table->string('brand',100)->nullable();
            $table->decimal('current_srp',16,2)->default(0.00)->nullable();
            $table->unsignedInteger('has_serial')->length(10)->default(0);
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
        Schema::dropIfExists('items');
    }
}
