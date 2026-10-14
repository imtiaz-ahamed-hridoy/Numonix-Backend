<?php

namespace Tests\Feature\Founder;

use App\Models\User;
use App\Models\FounderReviewSubmission;
use App\Mail\FounderReviewCompletionEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FounderReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_non_founder_cannot_access_review_form(): void
    {
        $user = User::factory()->create(['is_founder' => false]);

        $response = $this->actingAs($user)->get(route('founder.review.show'));

        $response->assertRedirect('/');
    }

    public function test_founder_can_view_review_form(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_review_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('founder.review.show'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.founder.review');
    }

    public function test_founder_submits_review_successfully(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_review_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('founder.review.submit'), [
            'rating' => 5,
            'feedback' => 'Amazing product for ear training!',
        ]);

        $response->assertRedirect(route('founder.review.thankyou'));

        $user->refresh();
        $this->assertTrue($user->founder_review_completed);

        $this->assertDatabaseHas('founder_review_submissions', [
            'user_id' => $user->id,
            'rating' => 5,
            'feedback' => 'Amazing product for ear training!',
        ]);

        Mail::assertSent(FounderReviewCompletionEmail::class, 1);
    }

    public function test_founder_already_completed_redirects_to_thankyou(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_review_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('founder.review.show'));

        $response->assertRedirect(route('founder.review.thankyou'));
    }

    public function test_non_founder_cannot_access_thankyou_page(): void
    {
        $user = User::factory()->create(['is_founder' => false]);

        $response = $this->actingAs($user)->get(route('founder.review.thankyou'));

        $response->assertRedirect('/');
    }

    public function test_founder_can_access_thankyou_page(): void
    {
        $user = User::factory()->create(['is_founder' => true]);

        $response = $this->actingAs($user)->get(route('founder.review.thankyou'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.founder.review-thankyou');
    }

    public function test_review_submission_validation_fails_without_rating(): void
    {
        $user = User::factory()->create([
            'is_founder' => true,
            'founder_review_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('founder.review.submit'), [
            'rating' => 0, // invalid: min is 1
            'feedback' => 'Too low rating test',
        ]);

        $response->assertSessionHasErrors('rating');
    }
}
