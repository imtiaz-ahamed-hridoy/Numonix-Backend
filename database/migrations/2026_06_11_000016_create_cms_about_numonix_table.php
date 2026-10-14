<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_about_numonix', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('heading');
            $table->text('intro_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_about_numonix');
    }
};
