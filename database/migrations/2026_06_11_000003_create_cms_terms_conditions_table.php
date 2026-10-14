<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_terms_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('last_updated_label')->default('January 2025'); // e.g. "January 2025"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_terms_conditions');
    }
};
