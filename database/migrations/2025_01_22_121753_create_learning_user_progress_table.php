<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable constraints so we can reference 'guest_devices' which is created later
        Schema::disableForeignKeyConstraints();

        Schema::create('learning_user_progress', function (Blueprint $table) {
            $table->id();

            // user_id is nullable to support guest tracking via guest_device_id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            $table->unsignedBigInteger('guest_device_id')->nullable();
            $table->string('guest_fingerprint', 64)->nullable()->index();
            $table->foreign('guest_device_id')->references('id')->on('guest_devices')->onDelete('set null');

            $table->foreignId('learning_id')->constrained('learnings')->cascadeOnDelete();

            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('last_viewed_page')->default(1);

            $table->timestamps();

            $table->index(['learning_id', 'is_completed']);
            $table->index('guest_device_id', 'learning_user_progress_guest_device_id_index');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_user_progress');
    }
};
