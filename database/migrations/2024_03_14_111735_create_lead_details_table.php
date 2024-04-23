<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('lead_id');

            $table->string('kitchen')->nullable();
            $table->string('pets')->nullable();
            $table->string('schools')->nullable();
            $table->string('budget')->nullable();
            $table->string('seen')->nullable();
            $table->date('move_in')->nullable();
            $table->string('cheque')->nullable();
            $table->string('furnish')->nullable();
            $table->string('upgraded')->nullable();
            $table->string('landscape')->nullable();
            $table->string('bathroom')->nullable();
            $table->string('bedroom')->nullable();
            $table->string('suite_bathroom')->nullable();
            $table->string('current_home')->nullable();
            $table->string('work_place')->nullable();
            $table->string('new_to_dubai')->nullable();
            $table->string('view')->nullable();
            $table->string('single_row')->nullable();
            $table->string('vastu')->nullable();
            $table->string('parking')->nullable();
            $table->string('floor')->nullable();
            $table->string('bua')->nullable();
            $table->string('plot_size')->nullable();
            $table->string('balcony')->nullable();
            $table->string('study')->nullable();
            $table->string('maid_room')->nullable();
            $table->string('white_goods')->nullable();
            $table->string('community')->nullable();
            $table->string('subcommunity')->nullable();
            $table->string('property')->nullable();
            $table->string('type')->nullable();
            $table->string('budget_from')->nullable();
            $table->string('budget_to')->nullable();
            $table->string('cashfinance')->nullable();
            $table->string('whentopurchase')->nullable();
            $table->string('finance')->nullable();
            $table->string('finance_with')->nullable();
            $table->string('money_here')->nullable();
            $table->string('viewed_other')->nullable();
            $table->string('offered_anything')->nullable();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
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
        Schema::dropIfExists('lead_details');
    }
}
