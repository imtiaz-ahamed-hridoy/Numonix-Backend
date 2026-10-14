<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ear_training_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ear_training_stage_id')->constrained('ear_training_stages')->cascadeOnDelete();
            
            $table->integer('level_number')->default(1);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Access overrides at level-scope
            $table->boolean('is_premium')->default(false);
            $table->string('guest_access_override')->default('Use Level Rule');
            
            // Generation parameters
            $table->enum('exercise_type', ['identify_function', 'identify_progression'])->default('identify_function');
            $table->json('function_patterns')->nullable();
            $table->integer('tempo_bpm')->default(80);
            $table->integer('midi_program')->default(0);
            $table->enum('chord_voicing', ['block', 'arpeggiated'])->default('block');
            $table->enum('chord_size', ['3', '4', '5'])->default('3');
            $table->enum('chord_purity', ['pure', 'mixed', 'any'])->default('pure');
            $table->enum('answer_display', ['leadership_only', 'with_influences'])->default('leadership_only');
            $table->enum('chord_regularity', ['regular', 'irregular', 'any'])->default('regular');
            $table->integer('bar_length')->default(4);
            $table->boolean('closes_on_following_one')->default(false);
            $table->integer('repetitions')->default(1);
            $table->integer('exercise_count')->default(10);
            
            $table->string('question_text_override')->nullable();
            
            // Completion content
            $table->string('completion_heading')->nullable();
            $table->text('completion_body')->nullable();
            $table->string('completion_footer')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ear_training_levels');
    }
};
