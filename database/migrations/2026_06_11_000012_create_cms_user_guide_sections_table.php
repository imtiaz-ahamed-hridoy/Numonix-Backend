<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_user_guide_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_guide_id')->constrained('cms_user_guides')->cascadeOnDelete();
            $table->string('heading');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_user_guide_sections');
    }
};
