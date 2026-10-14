<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ear_training_explanation_templates', function (Blueprint $table) {
            $table->id();

            // The type of template, as required by the automated model
            $table->enum('type', ['transition', 'pattern', 'contrast', 'hint', 'prompt']);

            // Transition template fields
            $table->string('from_function')->nullable()->comment('e.g. T, S, D, Tp, Sp, Dp, etc.');
            $table->string('to_function')->nullable();
            $table->text('phrase')->nullable()->comment('The explanatory phrase');

            // Pattern & Contrast template fields
            $table->string('pattern')->nullable()->comment('e.g. T-S-T');
            $table->string('title')->nullable();
            $table->text('body')->nullable();

            // Hint template fields
            $table->string('hint_text')->nullable()->comment('The wording shown in the hint button');
            $table->string('display_value')->nullable()->comment('The actual hint revealed on click');

            // Prompt template fields (default question text for an exercise type)
            $table->string('exercise_type')->nullable();
            $table->string('prompt_text')->nullable();

            $table->timestamps();

            // Indexes for fast lookup by the engine
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ear_training_explanation_templates');
    }
};
