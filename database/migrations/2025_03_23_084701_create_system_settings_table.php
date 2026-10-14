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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('number')->nullable();
            $table->string('system_name')->nullable();
            $table->string('address')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->longText('description')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->string('telegram_url')->nullable();

            // Update-alert flags + optional metadata. The mobile app queries
            // these via /api/app-update/soft and /api/app-update/force to
            // decide whether to show a soft or force update dialog.
            $table->boolean('soft_update_alert')->default(false)->comment('When true the mobile app shows a dismissible update dialog');
            $table->boolean('force_update_alert')->default(false)->comment('When true the mobile app forces the user to update before continuing');
            $table->string('latest_version')->nullable()->comment('Latest available app version, e.g. 2.1.0');
            $table->string('app_store_url')->nullable()->comment('App Store (iOS) link for the update dialog');
            $table->string('play_store_url')->nullable()->comment('Google Play Store (Android) link for the update dialog');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
