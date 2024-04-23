<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('refno')->unique();
            $table->string('external_refno')->nullable();
            $table->string('title')->nullable();
            $table->text('desc')->nullable();
            $table->text('brochure_desc')->nullable();
            $table->string('property_for');
            $table->string('property_type');
            $table->string('category_id');
            $table->integer('country_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('community_id')->nullable();
            $table->integer('sub_community_id')->nullable();
            $table->integer('tower_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->string('plot_no')->nullable();
            $table->string('plot_area')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('floor_no')->nullable();
            $table->string('bua')->nullable();
            $table->string('location')->nullable();
            $table->boolean('lead_gen')->default(false);
            $table->boolean('international')->default(false);
            $table->boolean('poa')->default(false);
            $table->integer('project_status_id')->nullable(); //completed
            $table->dateTime('completion_date')->nullable(); // if the project status is completed
            $table->string('parking')->nullable();
            $table->string('beds')->nullable();
            $table->string('baths')->nullable();
            $table->string('furnished')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('status_reason')->nullable();
            $table->boolean('hot')->default(false);
            $table->boolean('exclusive')->default(false);
            $table->string('price')->nullable();
            $table->string('frequency')->nullable();
            $table->string('occupancy_id')->nullable();
            $table->string('cheques')->nullable();
            $table->string('sort_order')->nullable();
            $table->string('currency')->default('AED');
            $table->integer('created_by');
            $table->integer('agent_id')->nullable();
            $table->integer('marketing_agent_id')->nullable();
            $table->integer('owner_id')->nullable();
            $table->string('rera_permit')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->dateTime('next_availability_date')->nullable();
            $table->dateTime('available_date')->nullable();
            $table->integer('developer_id')->nullable();
            $table->integer('updated_by')->nullable();
            $table->string('view')->nullable();
            $table->string('video_link')->nullable();
            $table->string('live_tour_link')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('lang')->default('en');
            $table->boolean('is_sold')->default(false);
            $table->boolean('is_let')->default(false);
            $table->string('import_source')->nullable();
            $table->string('import_file')->nullable();
            $table->string('import_old_data')->nullable();
            $table->string('import_type')->nullable();
            $table->foreign('business_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamp('published_at')->nullable();
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
        Schema::dropIfExists('listings');
    }
}
