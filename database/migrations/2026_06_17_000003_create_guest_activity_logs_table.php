<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_device_id')->nullable()->index();
            $table->foreign('guest_device_id')
                ->references('id')
                ->on('guest_devices')
                ->onDelete('set null');
            $table->string('session_id', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('endpoint');
            $table->string('content_type')->nullable(); // learning, ear_training, quiz
            $table->unsignedBigInteger('content_id')->nullable();
            $table->boolean('was_locked')->default(false);
            $table->boolean('is_authenticated')->default(false);
            $table->string('user_subscription_tier')->nullable(); // tier_1, tier_2, tier_3
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['session_id', 'created_at']);
            $table->index(['created_at']);
            $table->index(['content_type', 'content_id']);
            $table->index(['is_authenticated', 'created_at']);
            $table->index('was_locked', 'guest_activity_logs_was_locked_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_activity_logs');
    }
};
