<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_complete_glossary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('cms_complete_glossary_sections')->cascadeOnDelete();
            $table->string('title')->nullable(); // Some items have no title (just body)
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_complete_glossary_items');
    }
};
