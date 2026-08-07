<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('inventory', function (Blueprint $table) {
            $table->unique('order_id');
        });

        Schema::table('shipment', function (Blueprint $table) {
            $table->unique('order_id');
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->unique('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
         Schema::table('inventory', function (Blueprint $table) {
            $table->dropUnique('order_id');
        });

        Schema::table('shipment', function (Blueprint $table) {
            $table->dropUnique('order_id');
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->dropUnique('order_id');
        });
    }
};
