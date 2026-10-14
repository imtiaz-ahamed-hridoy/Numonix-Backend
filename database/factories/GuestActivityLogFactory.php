<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuestActivityLog>
 */
class GuestActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id'              => $this->faker->uuid(),
            'ip_address'              => $this->faker->ipv4(),
            'user_agent'              => $this->faker->userAgent(),
            'endpoint'                => '/api/' . $this->faker->word(),
            'content_type'            => null,
            'content_id'              => null,
            'was_locked'              => false,
            'is_authenticated'        => false,
            'user_subscription_tier'  => null,
            'user_id'                 => null,
            'created_at'              => now(),
        ];
    }
}
