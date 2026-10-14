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
        Schema::create('learnings', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['lesson', 'core_theory', 'advanced_insight', 'case_study', 'fnh_architecture'])->default('lesson');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->string('read_time')->nullable();
            $table->text('summary')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('learning_groups')->nullOnDelete();
            $table->string('thumbnail_image_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_premium')->nullable()->default(null);
            $table->boolean('is_guest_accessible')->nullable()->default(null);
            $table->enum('status', ['active', 'coming_soon', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(
                ['is_published', 'status', 'category'],
                'learnings_published_status_category_sort_index'
            );
        });

        Schema::create('learning_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_id')->constrained('learnings')->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->string('title')->nullable();
            $table->string('section_title')->nullable();
            $table->timestamps();
        });

        Schema::create('learning_page_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_page_id')->constrained('learning_pages')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->string('type', 50);
            $table->json('data')->nullable();
            $table->string('file_url')->nullable();
            $table->timestamps();

            $table->index(['learning_page_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_page_elements');
        Schema::dropIfExists('learning_pages');
        Schema::dropIfExists('learnings');
    }
};
