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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
             $table->unsignedBigInteger('guest_device_id')->nullable()->index();
            $table->foreign('guest_device_id')
                ->references('id')
                ->on('guest_devices')
                ->onDelete('set null');
            $table->string('guest_fingerprint', 64)->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('attempted_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->decimal('score', 5, 2)->default(0);
            $table->json('answers_data')->nullable(); // Store user's specific answers
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
