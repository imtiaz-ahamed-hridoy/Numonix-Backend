<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores per-user checked state for checklist element items.
     */
    public function up(): void
    {
        Schema::create('learning_checklist_progress', function (Blueprint $table) {
            $table->id();
            // user_id is nullable to support guest tracking via guest_device_id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('guest_device_id')->nullable();
            $table->string('guest_fingerprint', 64)->nullable()->index()->comment('SHA-256 hash of IP+UserAgent for guest identification');
            $table->foreign('guest_device_id')->references('id')->on('guest_devices')->onDelete('set null');
            $table->foreignId('element_id')->constrained('learning_page_elements')->cascadeOnDelete();
            $table->json('checked_items')->nullable(); // array of checked item indexes
            $table->timestamps();
            $table->index('guest_device_id', 'learning_checklist_progress_guest_device_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_checklist_progress');
    }
};
