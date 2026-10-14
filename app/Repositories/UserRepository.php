<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository implements UserRepositoryInterface
{
    /**
     * The User model instance.
     */
    protected User $model;

    /**
     * UserRepository constructor.
     *
     * @param  User  $model  The User model instance to work with.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve all users from the database.
     *
     * @return Collection Collection of User models.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a user by its primary key (ID).
     *
     * @param  int  $id  The ID of the user to find.
     * @return User|null The User model if found, otherwise null.
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find a user by their email address.
     *
     * @param  string  $email  The email address to search for.
     * @return User|null The User model if found, otherwise null.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }


    /**
     * Get user registration counts grouped by date for the last N days.
     *
     * @param  int  $days  Number of days to include (default 30)
     * @return array<string, int>  Date => count pairs
     */
    public function getRegistrationTrend(int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        return DB::table('users')
            ->where('role', '!=', 'Admin')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Get user counts grouped by subscription tier.
     *
     * @return array<string, int>  Tier => count pairs
     */
    public function getUserTierDistribution(): array
    {
        $users = DB::table('users')
            ->where('role', '!=', 'Admin')
            ->select('subscription_tier', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription_tier')
            ->pluck('count', 'subscription_tier')
            ->toArray();

        return [
            'tier_1' => ($users['tier_1'] ?? 0) + ($users[''] ?? 0),
            'tier_2' => $users['tier_2'] ?? 0,
            'tier_3' => $users['tier_3'] ?? 0,
        ];
    }

    /**
     * Create a new user with the provided data.
     * Password will be hashed automatically if provided.
     *
     * @param  array  $userData  The data to create the user with.
     * @return User The newly created User model.
     */
    public function create(array $userData): User
    {
        if (isset($userData['password'])) {
            $userData['password'] = bcrypt($userData['password']);
        }

        $user = new $this->model;
        $user->forceFill($userData);
        $user->save();

        return $user;
    }

    /**
     * Update an existing user with new data.
     * Password will be hashed automatically if provided.
     *
     * @param  User  $user  The User model instance to update.
     * @param  array  $userData  The data to update the user with.
     * @return bool True if update was successful, false otherwise.
     */
    public function update(User $user, array $userData): bool
    {
        if (isset($userData['password'])) {
            $userData['password'] = bcrypt($userData['password']);
        }

        return $user->forceFill($userData)->save();
    }

    /**
     * Mark the user's email as verified by setting the email_verified_at timestamp.
     *
     * @param  User  $user  The User model to update.
     * @return bool True if the update was successful, false otherwise.
     */
    public function markEmailAsVerified(User $user): bool
    {
        return $user->forceFill(['email_verified_at' => now()])->save();
    }

    /**
     * Create a password reset token for the given email.
     *
     * @param  string  $email  The email address for which to create the reset token.
     * @return string token.
     */
    public function createResetPasswordToken(string $email): string
    {
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token), // Store as hash - never plain-text
            'created_at' => now(),
        ]);

        return $token; // Return the plain token to the caller (sent via email)
    }

    /**
     * Find a password reset token record by email and token.
     *
     * @param  string  $email  The email address associated with the token.
     * @param  string  $token  The password reset token to find.
     * @return object|null The token record if found, otherwise null.
     */
    public function findResetPasswordToken(string $email, string $token): ?object
    {
        // Retrieve the latest token record for this email
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        // Hash::check prevents timing attacks during comparison
        if ($record && Hash::check($token, $record->token)) {
            return $record;
        }

        return null;
    }

    /**
     * Delete a password reset token record by its token value.
     *
     * @param  string  $email  The password reset token to delete.
     * @return bool True if at least one record was deleted, false otherwise.
     */
    public function deleteResetPasswordTokenByEmail(string $email): bool
    {
        return DB::table('password_reset_tokens')->where('email', $email)->delete() > 0;
    }

    /**
     * Get conversion funnel stats: total users, tier 1, subscribed, new registrations.
     *
     * @param  \Illuminate\Support\Carbon  $since  Date threshold for new registrations
     * @return array{total_users: int, tier1_users: int, subscribed_users: int, new_registrations: int}
     */
    public function getConversionFunnelStats(\Illuminate\Support\Carbon $since): array
    {
        $stats = DB::table('users')
            ->where('role', '!=', 'Admin')
            ->selectRaw('
                COUNT(*) as total_users,
                SUM(CASE WHEN subscription_tier IS NULL OR subscription_tier = ? THEN 1 ELSE 0 END) as tier1_users,
                SUM(CASE WHEN subscription_tier IS NOT NULL AND subscription_tier != ? THEN 1 ELSE 0 END) as subscribed_users,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_registrations
            ', ['tier_1', 'tier_1', $since])
            ->first();

        return [
            'total_users' => (int) ($stats->total_users ?? 0),
            'tier1_users' => (int) ($stats->tier1_users ?? 0),
            'subscribed_users' => (int) ($stats->subscribed_users ?? 0),
            'new_registrations' => (int) ($stats->new_registrations ?? 0),
        ];
    }

    /**
     * Find a user by ID or throw an exception.
     */
    public function findOrFail(int $id): User
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get all users ordered by latest (created_at DESC).
     */
    public function getLatest(): Collection
    {
        return $this->model->latest()->get();
    }

    /**
     * Get active users with id, name, email ordered by name.
     */
    public function getActiveUsersList(): Collection
    {
        return $this->model->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Get users by an array of IDs.
     */
    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}

