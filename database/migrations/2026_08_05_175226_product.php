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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('ProductID');
            $table->string('ProductName');
            $table->string('SupplierID');
            $table->string('CategoryID');
            $table->string('QuantityPerUnit');
            $table->decimal('UnitPrice', 18, 4);
            $table->unsignedInteger("UnitsInStock");
            $table->unsignedInteger("UnitsOnOrder");
            $table->unsignedInteger("ReorderLevel");
            $table->boolean("Discontinued");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('products');
    }
};
