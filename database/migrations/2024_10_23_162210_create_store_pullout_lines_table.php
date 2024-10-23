<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorePulloutLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_pullout_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_pullouts_id')->constrained()->onDelete('cascade');
            $table->string('item_code',100)->nullable()->index();
            $table->unsignedInteger('qty')->nullable();
            $table->decimal('unit_price', 16, 2)->default('0.00');
            $table->text('problems')->nullable();
            $table->text('problem_details')->nullable();
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
        Schema::dropIfExists('store_pullout_lines');
    }
}
