<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('refno')->unique();
            $table->string('old_refno');
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('nationality')->nullable();
            $table->date('dob')->nullable();
            $table->string('company')->nullable();
            $table->string('designation')->nullable();
            $table->string('religion')->nullable();
            $table->string('lang')->default('en');
            $table->string('website')->nullable();
            $table->integer('source_id')->nullable();
            $table->integer('sub_source_id')->nullable();
            $table->boolean('status')->default(true);
            $table->string('photo')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('address')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('owners');
    }
}
