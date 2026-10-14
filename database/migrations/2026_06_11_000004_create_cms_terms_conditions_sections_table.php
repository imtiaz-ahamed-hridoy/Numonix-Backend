<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_terms_conditions_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terms_conditions_id')->constrained('cms_terms_conditions')->cascadeOnDelete();
            $table->integer('section_number')->default(1);
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_terms_conditions_sections');
    }
};
