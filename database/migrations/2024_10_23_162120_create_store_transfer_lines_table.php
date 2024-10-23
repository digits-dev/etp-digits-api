<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTransferLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_transfers_id')->constrained()->onDelete('cascade');
            $table->string('item_code',100)->nullable();
            $table->unsignedInteger('qty')->nullable();
            $table->decimal('unit_price', 16, 2)->default('0.00');
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
        Schema::dropIfExists('store_transfer_lines');
    }
}
