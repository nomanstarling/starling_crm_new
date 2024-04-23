<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_queues', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->longText('body')->nullable();
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
        Schema::dropIfExists('leads_queues');
    }
}
