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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'imported_at')) {
                $table->timestamp('imported_at')->nullable()->after('is_founder');
            }
            if (!Schema::hasColumn('users', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('imported_at');
            }
            if (!Schema::hasColumn('users', 'activation_token')) {
                $table->string('activation_token', 80)->nullable()->unique()->after('activated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['imported_at', 'activated_at', 'activation_token']);
        });
    }
};
