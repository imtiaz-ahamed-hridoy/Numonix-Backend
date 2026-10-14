<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ear_training_user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ear_training_user_progress_id')
                  ->constrained(table: 'ear_training_user_progress', indexName: 'et_ua_prog_id_fk')
                  ->cascadeOnDelete();
            $table->integer('exercise_index')->default(0);
            $table->string('user_answer')->nullable();
            $table->string('correct_answer');
            $table->boolean('is_correct');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ear_training_user_answers');
    }
};
