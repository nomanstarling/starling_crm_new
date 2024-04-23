<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('port');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('answer_date')->nullable();
            $table->string('direction')->nullable();
            $table->string('source')->nullable();
            $table->string('ip')->nullable();
            $table->string('destination')->nullable();
            $table->string('hang_side')->nullable();
            $table->string('reason')->nullable();
            $table->string('duration')->nullable();
            $table->string('codec')->nullable();
            $table->string('rtp_send')->nullable();
            $table->string('rtp_recv')->nullable();
            $table->string('loss_rate')->nullable();
            $table->string('BCCH')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
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
        Schema::dropIfExists('crm_calls');
    }
}
