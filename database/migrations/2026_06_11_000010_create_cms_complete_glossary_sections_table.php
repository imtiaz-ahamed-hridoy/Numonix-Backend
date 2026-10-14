<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_complete_glossary_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complete_glossary_id')->constrained('cms_complete_glossaries')->cascadeOnDelete();
            $table->string('label'); // e.g. "I. FIXED NUMERIC SYSTEM"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_complete_glossary_sections');
    }
};
