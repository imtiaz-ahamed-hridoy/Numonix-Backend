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
        Schema::create('booking_products', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('status')->default('CONFIRMED');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->json('bokun_raw_data')->nullable();
            $table->timestamps();
            $table->softDeletes(); // <-- Soft delete column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_products');
    }
};
