<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\LogStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId("order_id")
                ->references('id')
                ->on('orders');
            $table->string("listener")->nullable();
            $table->enum("log_status", LogStatusEnum::values());
            $table->string("message")->nullable();
            $table->unsignedInteger("attempt")->nullable();
            $table->dateTime("processed_at");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
