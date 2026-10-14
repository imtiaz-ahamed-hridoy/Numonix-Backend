<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ear_training_stages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('patterns_label')->nullable();
            $table->text('description')->nullable();
            $table->string('accent_colour')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_locked_by_default')->default(false);
            $table->json('unlock_criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->string('guest_access_override')->default('Use Level Rule');
            $table->string('next_stage_teaser')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ear_training_stages');
    }
};
