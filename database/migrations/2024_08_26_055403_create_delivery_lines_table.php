<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('deliveries_id')->nullable();
            $table->unsignedInteger('line_number')->length(10)->nullable();
            $table->string('ordered_item', 60)->nullable();
            $table->unsignedInteger('ordered_item_id')->length(10)->nullable();
            $table->unsignedInteger('shipped_quantity')->length(10)->nullable();
            $table->unsignedTinyInteger('line_status')->default(0)->comment('0-pending,1-received');
            $table->unsignedInteger('updated_by')->nullable();
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
        Schema::dropIfExists('delivery_lines');
    }
}
