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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('dob')->nullable();
            $table->text('avatar')->nullable();
            $table->string('password');
            $table->string('socialite_id')->nullable();
            $table->string('socialite_provider')->nullable();
            $table->string('socialite_token')->nullable();
            $table->string('socialite_refresh_token')->nullable();
            $table->string('socialite_expires_at')->nullable();
            $table->boolean('is_age_verified')->default(false);
            $table->boolean('is_agree')->default(true);
            $table->enum('role', ['Admin', 'User'])->default('User');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_subscribed')->default(false);
            $table->enum('subscription_tier', ['tier_1', 'tier_2', 'tier_3'])->default('tier_1');
            $table->string('paddle_id')->nullable()->unique()->comment('Paddle customer ID');
            $table->text('paddle_customer')->nullable()->comment('Cashier internal customer data');
            $table->timestamp('trial_ends_at')->nullable()->comment('Cashier general trial end');
            $table->string('plan_name')->nullable()->default('Tier 1');
            $table->date('next_billing_date')->nullable();
            
            // Founder programme fields
            $table->boolean('is_founder')->default(false)->index()->comment('True if user is a Founder programme member');
            $table->timestamp('founder_trial_ends_at')->nullable()->comment('30-day trial expiry for founders');
            $table->boolean('founder_review_completed')->default(false)->comment('Has founder submitted the review form (non-gating)');
            $table->text('founder_checkout_link')->nullable()->comment('Unique Paddle checkout link for £24.99 offer');
            $table->timestamp('founder_day25_sent_at')->nullable()->comment('When the day-25 trial-ending email was sent');
            $table->timestamp('founder_day30_sent_at')->nullable()->comment('When the day-30 expiry email was sent');
            $table->timestamp('founder_converted_at')->nullable()->comment('When founder paid £24.99 and converted');
            
            $table->boolean('is_online')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('reset_code')->nullable();
            $table->timestamp('reset_code_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('email', 'users_email_index');
            $table->index(['role', 'status'], 'users_role_status_index');
            $table->index('subscription_tier', 'users_subscription_tier_index');
            $table->index('created_at', 'users_created_at_index');
            $table->index('founder_trial_ends_at', 'users_founder_trial_ends_at_index');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
