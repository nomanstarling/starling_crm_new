<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('refno')->unique();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('user_name')->unique();
            $table->string('email')->unique();
            $table->string('email_secondary')->nullable();
            $table->string('phone')->unique();
            $table->string('phone_secondary')->nullable();
            $table->string('designation')->nullable();
            $table->string('gender')->nullable();
            $table->string('extention')->nullable();
            $table->string('rera_no')->nullable();
            $table->string('brn')->nullable();
            $table->boolean('status')->default(true)->nullable();
            $table->string('photo')->nullable();
            $table->string('lang')->default('en');
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('desc')->nullable();
            $table->integer('calls_goal_month')->nullable();
            $table->integer('off_market_listing_goal_month')->nullable();
            $table->integer('published_listing_goal_month')->nullable();
            $table->integer('rental_percent')->default(0)->nullable();
            $table->integer('sales_percent')->default(0)->nullable();
            $table->integer('yearly_target')->default(0)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('google_access_token')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
