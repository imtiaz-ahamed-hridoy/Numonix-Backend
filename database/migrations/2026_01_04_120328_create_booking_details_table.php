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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('booking_products')
                ->onDelete('cascade');
            $table->unsignedBigInteger('package_id');
            $table->string('type')->default('activity');
            $table->string('title')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->integer('quantity')->default(1);
            $table->json('bokun_raw_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
