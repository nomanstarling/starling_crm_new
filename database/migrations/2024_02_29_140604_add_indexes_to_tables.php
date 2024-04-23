<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('name');
            $table->index('email');
            $table->index('refno');
            $table->index('phone');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->index('refno');
            $table->index('external_refno');
            $table->index('published_at');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('furnished');
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->index('refno');
            $table->index('name');
            $table->index('email');
            $table->index('phone');
            $table->index('whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('email');
            $table->dropIndex('refno');
            $table->dropIndex('phone');
            $table->dropIndex('created_at');
            $table->dropIndex('updated_at');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('refno');
            $table->dropIndex('external_refno');
            $table->dropIndex('published_at');
            $table->dropIndex('created_at');
            $table->dropIndex('updated_at');
            $table->dropIndex('furnished');
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->dropIndex('refno');
            $table->dropIndex('name');
            $table->dropIndex('email');
            $table->dropIndex('phone');
            $table->dropIndex('whatsapp');
        });
    }
}
