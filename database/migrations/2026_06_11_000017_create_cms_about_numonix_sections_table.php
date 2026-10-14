<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_about_numonix_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_numonix_id')->constrained('cms_about_numonix')->cascadeOnDelete();
            $table->string('heading');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_about_numonix_sections');
    }
};
