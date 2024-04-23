<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('refno')->unique();
            $table->string('old_refno')->nullable();
            $table->string('listing_refno')->nullable();
            $table->string('listing_id')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('finance')->nullable();
            $table->string('priority')->nullable();
            $table->string('source_id')->nullable();
            $table->string('sub_source_id')->nullable();
            $table->string('lead_type')->nullable();
            $table->string('status_id')->nullable();
            $table->string('sub_status_id')->nullable();
            $table->datetime('enquiry_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('emailopt')->nullable();
            $table->string('hotlead')->nullable();
            $table->datetime('assigned_date')->nullable();
            $table->datetime('accepted_date')->nullable();
            $table->integer('reassigned_from')->nullable();
            $table->integer('agent_id')->nullable();
            $table->string('seller_agent')->nullable();
            $table->string('buyer_agent')->nullable();
            $table->string('seller_contact')->nullable();
            $table->string('buyer_contact')->nullable();
            $table->string('seller_commission_percent')->nullable();
            $table->string('buyer_commission_percent')->nullable();
            $table->string('seller_commission_amount')->nullable();
            $table->string('buyer_commission_amount')->nullable();

            $table->string('deal_price')->nullable();

            $table->string('assign_status')->nullable();
            $table->string('accept_status')->nullable();

            $table->enum('assign_status', ['Unassigned', 'Assigned', 'Accepted', 'Pooled'])->default('Unassigned')->nullable()->change();
            $table->enum('accept_status', ['Accepted', 'Declined', 'Pending'])->default('Pending')->nullable()->change();

            $table->string('comment')->nullable();
            $table->string('import_source')->nullable();
            $table->string('ipaddress')->nullable();
            $table->string('referal_url')->nullable();

            $table->string('pfmessage')->nullable();
            $table->string('external_agent')->nullable();
            $table->string('move_in_date')->nullable();
            $table->string('last_call')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('lead_stage')->nullable();
            $table->string('leads_exported')->nullable();
            $table->string('lmstocrm')->nullable();
            $table->string('parent_lead')->nullable();
            $table->string('last_match')->nullable();
            $table->string('email_send')->nullable();
            $table->integer('campaign_id')->nullable();

            $table->string('processlog')->nullable();
            $table->string('hash')->nullable();

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
        Schema::dropIfExists('leads');
    }
}
