<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePulloutLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pullout_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pullouts_id')->constrained()->onDelete('cascade');
            $table->string('item_code',100)->nullable();
            $table->unsignedInteger('qty')->nullable();
            $table->text('problems')->nullable();
            $table->text('problem_details')->nullable();
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
        Schema::dropIfExists('pullout_lines');
    }
}
