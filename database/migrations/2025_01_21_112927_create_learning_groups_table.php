<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_groups', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->default('case_study'); // case_study or fnh_architecture
            $table->string('label');                                // Foundation, Intermediate, Advanced
            $table->string('color', 20)->nullable();                // #059669, #2457E6, #DC2626
            $table->string('description')->nullable();
            $table->boolean('is_premium')->default(false);          // Group-level premium flag (Tier 1)
            $table->boolean('tier2_premium')->default(false);       // Tier 2 gate
            $table->boolean('tier3_premium')->default(false);       // Tier 3 gate
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_groups');
    }
};
