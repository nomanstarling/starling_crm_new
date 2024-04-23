<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            
            $table->string('name');
            $table->string('target_name')->nullable();

            $table->string('community_id')->nullable();
            $table->string('sub_community_id')->nullable();
            $table->string('tower_id')->nullable();
            $table->string('source_id')->nullable();
            
            $table->integer('assignment_pointer')->default(0)->nullable();

            $table->string('match_count')->nullable();
            $table->string('auto_assign')->nullable();
            $table->string('auto_assign_after')->nullable();

            $table->text('agents')->nullable();

            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('campaigns');
    }
}
