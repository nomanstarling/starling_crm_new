<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('object')->nullable(); // leads / listings / users etc
            $table->integer('object_id');
            $table->string('object_type');
            $table->string('path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->string('alt')->nullable();
            $table->string('sort_order')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('featured')->default(true);
            $table->boolean('floor_plan')->default(true);
            $table->boolean('watermark')->default(true);
            $table->boolean('is_cropped')->default(true);
            $table->string('tag')->nullable();
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
        Schema::dropIfExists('media_galleries');
    }
}
