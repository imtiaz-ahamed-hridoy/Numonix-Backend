<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ear_training_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('guest_device_id')->nullable();
            $table->foreign('guest_device_id')->references('id')->on('guest_devices')->nullOnDelete();
            $table->foreignId('ear_training_level_id')->constrained('ear_training_levels')->cascadeOnDelete();
            
            $table->integer('total_exercises')->default(0);
            $table->integer('completed_exercises')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('score_percentage')->default(0);
            $table->integer('attempts')->default(0);
            $table->enum('status', ['not_started', 'completed'])->default('not_started');
            $table->timestamps();
            
            $table->unique(['user_id', 'ear_training_level_id'], 'et_up_uid_lvl_idx');
            $table->unique(['guest_device_id', 'ear_training_level_id'], 'et_up_gid_lvl_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ear_training_user_progress');
    }
};
