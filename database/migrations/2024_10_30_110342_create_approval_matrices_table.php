<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalMatricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_matrix', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cms_privileges_id')->unsigned();
            $table->integer('cms_users_id')->unsigned();
            $table->integer('channel_id')->unsigned()->nullable();
            $table->text('store_list')->nullable();
            $table->string('channels_visibility',50)->nullable();
            $table->string('status',10)->default('ACTIVE')->nullable();
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
        Schema::dropIfExists('approval_matrix');
    }
}
