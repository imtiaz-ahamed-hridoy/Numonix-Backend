<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('token')->unique();
            $table->string('guest_fingerprint', 64)->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_devices');
    }
};
