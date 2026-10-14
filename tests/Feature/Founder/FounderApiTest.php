<?php

namespace Tests\Feature\Founder;

use App\Models\User;
use App\Models\FounderReviewSubmission;
use App\Mail\FounderReviewCompletionEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FounderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_guest_cannot_access_founder_status(): void
    {
        $response = $this->getJson('/api/founder/status');
        $response->assertStatus(401);
    }

    public function test_regular_user_founder_status(): void
    {
        $user = User::factory()->create([
            'is_founder' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/founder/status');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'is_founder'          => false,
                'founder_trial_active'=> false,
            ],
        ]);
    }

    public function test_active_founder_trial_status(): void
    {
        $user = User::factory()->create([
            'is_founder'            => true,
            'founder_trial_ends_at' => Carbon::now()->addDays(15),
            'founder_review_completed' => false,
            'founder_converted_at'  => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/founder/status');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'is_founder'              => true,
                'founder_trial_active'    => true,
                'founder_review_completed'=> false,
            ],
        ]);
    }

    public function test_expired_founder_trial_status(): void
    {
        // Trial ended 3 days ago — user is still a founder but trial is no longer active
        $user = User::factory()->create([
            'is_founder'            => true,
            'founder_trial_ends_at' => Carbon::now()->subDays(3),
            'founder_converted_at'  => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/founder/status');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'is_founder'          => true,
                'founder_trial_active'=> false, // expired — no longer active
            ],
        ]);
    }

    public function test_non_founder_cannot_submit_review(): void
    {
        $user = User::factory()->create([
            'is_founder' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/founder/review', [
            'rating'   => 5,
            'feedback' => 'Nice app',
        ]);

        $response->assertStatus(403);
    }

    public function test_founder_submits_review_successfully(): void
    {
        $user = User::factory()->create([
            'is_founder'               => true,
            'founder_review_completed' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/founder/review', [
            'rating'   => 5,
            'feedback' => 'Incredible ear training system!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'data'   => [
                'review_completed' => true,
            ],
        ]);

        $user->refresh();
        $this->assertTrue($user->founder_review_completed);

        $this->assertDatabaseHas('founder_review_submissions', [
            'user_id' => $user->id,
            'rating'  => 5,
            'feedback'=> 'Incredible ear training system!',
        ]);

        Mail::assertSent(FounderReviewCompletionEmail::class, 1);
    }

    public function test_founder_cannot_submit_duplicate_review(): void
    {
        $user = User::factory()->create([
            'is_founder'               => true,
            'founder_review_completed' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/founder/review', [
            'rating'   => 4,
            'feedback' => 'Trying to submit again',
        ]);

        $response->assertStatus(400);
    }

    public function test_founder_review_validation_errors(): void
    {
        $user = User::factory()->create([
            'is_founder'               => true,
            'founder_review_completed' => false,
        ]);

        Sanctum::actingAs($user);

        // Missing rating
        $response = $this->postJson('/api/founder/review', [
            'feedback' => 'No rating provided',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);

        // Invalid rating (out of 1-5 range)
        $response2 = $this->postJson('/api/founder/review', [
            'rating' => 6,
        ]);

        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['rating']);
    }
}
