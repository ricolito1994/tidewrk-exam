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
        Schema::table('logs', function (Blueprint $table) {
            $table->text('message')->change();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dateTime('reserved_at')->nullable()->change();
        });

        Schema::table('shipment', function (Blueprint $table) {
            $table->dateTime('shipped_at')->nullable()->change();
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('logs', function (Blueprint $table) {
            $table->string('message')->change();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dateTime('reserved_at')->nullable(false)->change();
        });

        Schema::table('shipment', function (Blueprint $table) {
            $table->dateTime('shipped_at')->nullable(false)->change();
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable(false)->change();
        });
    }
};
