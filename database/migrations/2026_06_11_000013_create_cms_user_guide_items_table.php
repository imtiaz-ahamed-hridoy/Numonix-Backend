<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_user_guide_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('cms_user_guide_sections')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('anchor_id')->nullable();
            $table->text('body')->nullable();
            $table->json('images')->nullable();
            $table->json('keys')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_user_guide_items');
    }
};
