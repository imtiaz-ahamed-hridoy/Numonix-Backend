<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users
     */
    public function all(): Collection;

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;



    /**
     * Get user registration counts grouped by date for the last N days.
     *
     * @return array<string, int> e.g. ['2026-07-12' => 5, '2026-07-13' => 3]
     */
    public function getRegistrationTrend(int $days): array;

    /**
     * Get user counts grouped by subscription tier.
     *
     * @return array<string, int> e.g. ['tier_1' => 100, 'tier_2' => 25, 'tier_3' => 10]
     */
    public function getUserTierDistribution(): array;

    /**
     * Create new user
     */
    public function create(array $userData): User;

    /**
     * Update user
     */
    public function update(User $user, array $userData): bool;

    /**
     * Mark user email as verified
     */
    public function markEmailAsVerified(User $user): bool;

    /**
     * Create reset password token
     */
    public function createResetPasswordToken(string $email): string;

    /**
     * Find a password reset token record by email and token.
     *
     * @param  string  $email  The email address associated with the token.
     * @param  string  $token  The password reset token to find.
     * @return object|null The token record if found, or null if not found.
     */
    public function findResetPasswordToken(string $email, string $token): ?object;

    /**
     * Delete a password reset token by its token string.
     *
     * @return bool True if deletion was successful, false otherwise.
     */
    public function deleteResetPasswordTokenByEmail(string $email): bool;

    /**
     * Get conversion funnel stats: total users, tier 1, subscribed, new registrations.
     *
     * @param  \Illuminate\Support\Carbon  $since  Date threshold for new registrations
     * @return array{total_users: int, tier1_users: int, subscribed_users: int, new_registrations: int}
     */
    public function getConversionFunnelStats(\Illuminate\Support\Carbon $since): array;

    /**
     * Find a user by ID or throw an exception.
     *
     * @param  int  $id
     * @return User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): User;

    /**
     * Get all users ordered by latest (created_at DESC).
     *
     * @return Collection<int, User>
     */
    public function getLatest(): Collection;

    /**
     * Get active users with id, name, email ordered by name.
     *
     * @return Collection<int, User>
     */
    public function getActiveUsersList(): Collection;

    /**
     * Get users by an array of IDs.
     *
     * @param  array  $ids
     * @return Collection<int, User>
     */
    public function getByIds(array $ids): Collection;
}

