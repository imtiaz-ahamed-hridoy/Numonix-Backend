<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_modules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('icon', 255)->nullable();
            $table->string('badge_text', 50)->nullable();
            $table->string('cta_text', 50)->default('Start');
            $table->enum('module_type', ['learning_category', 'ear_training', 'external'])->default('learning_category');
            $table->string('module_key', 100);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_modules');
    }
};
