<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemSerialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_lines_id')->constrained()->onDelete('cascade')->nullable();
            $table->string('serial_number');
            $table->unsignedTinyInteger('status')->default(0)->comment('0-pending,1-received');
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
        Schema::dropIfExists('item_serials');
    }
}
