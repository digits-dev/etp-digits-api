<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('document_number',50)->nullable();
            $table->string('received_document_number',50)->nullable();
            $table->string('ref_number',50)->nullable();
            $table->text('memo')->nullable();
            $table->date('transfer_date')->nullable();
            $table->date('transfer_schedule_date')->nullable();
            $table->string('transaction_type',10)->nullable();
            $table->string('wh_from',10)->nullable();
            $table->string('wh_to',10)->nullable();
            $table->string('hand_carrier',150)->nullable();
            $table->unsignedInteger('reasons_id')->length(10)->nullable();
            $table->unsignedInteger('transport_types_id')->length(10)->nullable();
            $table->unsignedInteger('channels_id')->length(10)->nullable();
            $table->unsignedInteger('stores_id')->length(10)->nullable();
            $table->unsignedInteger('stores_id_destination')->length(10)->nullable();
            $table->unsignedInteger('status')->default(0)->comment('0-pending,1-approved,2-processing,3-received')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('scheduled_by')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedInteger('confirmed_by')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->unsignedInteger('rejected_by')->nullable();
            $table->dateTime('rejected_at')->nullable();
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
        Schema::dropIfExists('store_transfers');
    }
}
