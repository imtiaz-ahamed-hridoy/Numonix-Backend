<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_user_guides', function (Blueprint $table) {
            $table->id();
            $table->enum('tab', ['getting_started', 'harmonic_builder'])->unique();
            $table->string('heading')->nullable();
            $table->text('intro_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_user_guides');
    }
};
