<?php

namespace Tests\Feature\Auth;

use App\Exceptions\GuestDeviceSyncException;
use App\Models\EarTrainingLevel;
use App\Models\EarTrainingQuiz;
use App\Models\EarTrainingQuizAttempt;
use App\Models\GuestDevice;
use App\Models\LearningChecklistProgress;
use App\Models\LearningUserProgress;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GuestDeviceSyncService;
use App\Services\GuestFingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Feature tests for the /api/register endpoint.
 *
 * Uses RefreshDatabase so every test starts with a clean MySQL schema.
 * Mail is faked so OTP emails never actually send.
 */
class ApiRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private GuestFingerprintService $fingerprintService;

    protected function setUp(): void
    {
        parent::setUp();
        // Prevent real emails from being sent during tests
        Mail::fake();
        $this->fingerprintService = app(GuestFingerprintService::class);
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------------*/

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Test User',
            'email'                 => 'testuser@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'is_age_verified'       => true,
        ], $overrides);
    }

    /**
     * Create a GuestDevice with a correct HMAC fingerprint.
     *
     * IMPORTANT: The fingerprint is computed server-side using
     * GuestFingerprintService::compute(device_id, token). This ensures
     * the RegistrationService's hash_equals() verification passes.
     */
    private function makeGuestDevice(array $overrides = []): GuestDevice
    {
        $deviceId = 'test_device_' . uniqid();
        $token    = 'test_token_'  . uniqid();

        $defaults = [
            'device_id'         => $deviceId,
            'token'             => $token,
            'guest_fingerprint' => $this->fingerprintService->compute($deviceId, $token),
        ];

        return GuestDevice::create(array_merge($defaults, $overrides));
    }

    /* ------------------------------------------------------------------
     * Registration without guest device
     * ----------------------------------------------------------------*/

    /** @test */
    public function it_registers_a_new_user_without_a_guest_device(): void
    {
        $response = $this->postJson('/api/register', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['user', 'guest_synced'],
                'message',
                'status',
            ])
            ->assertJsonPath('data.guest_synced', false);

        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    /** @test */
    public function it_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_422_when_email_is_already_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/register', $this->validPayload([
            'email' => 'taken@example.com',
        ]));

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_422_when_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/register', $this->validPayload([
            'password_confirmation' => 'DifferentPassword@123',
        ]));

        $response->assertStatus(422);
    }

    /* ------------------------------------------------------------------
     * Registration WITH guest device sync
     * ----------------------------------------------------------------*/

    /** @test */
    public function it_syncs_guest_ear_training_attempts_on_registration(): void
    {
        $device = $this->makeGuestDevice();
        $stage = \App\Models\EarTrainingStage::firstOrCreate(['title' => 'Test Stage'], ['sort_order' => 1]);
        $level = \App\Models\EarTrainingLevel::create(['ear_training_stage_id' => $stage->id, 'name' => 'Beginner', 'level_number' => 1, 'is_premium' => false]);

        \App\Models\EarTrainingUserProgress::create([
            'guest_device_id'       => $device->id,
            'ear_training_level_id' => $level->id,
            'is_completed'          => true,
            'best_accuracy_percentage' => 80.00,
        ]);

        $response = $this->postJson('/api/register', $this->validPayload([
            'device_id'         => $device->device_id,
            'token'             => $device->token,
            'guest_fingerprint' => $device->guest_fingerprint,
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.guest_synced', true);

        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user);

        // Attempt must now belong to the user
        $this->assertDatabaseHas('ear_training_user_progress', [
            'ear_training_level_id' => $level->id,
            'user_id'              => $user->id,
        ]);

        // GuestDevice must be deleted after cleanup
        $this->assertDatabaseMissing('guest_devices', ['id' => $device->id]);
    }

    /** @test */
    public function it_syncs_guest_quiz_attempts_on_registration(): void
    {
        $device = $this->makeGuestDevice();

        $learning = \App\Models\Learning::create([
            'title' => 'Test Learning',
            'slug' => 'test-learning-' . uniqid(),
            'category' => 'lesson',
            'is_published' => true,
            'status' => 'active',
        ]);
        $quiz = \App\Models\Quiz::create([
            'learning_id' => $learning->id,
            'status' => 'published',
        ]);

        QuizAttempt::create([
            'guest_device_id'      => $device->id,
            'quiz_id'              => $quiz->id,
            'total_questions'      => 5,
            'attempted_questions'  => 5,
            'correct_answers'      => 4,
            'score'                => 80.00,
        ]);

        $response = $this->postJson('/api/register', $this->validPayload([
            'device_id'         => $device->device_id,
            'token'             => $device->token,
            'guest_fingerprint' => $device->guest_fingerprint,
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.guest_synced', true);

        $user = User::where('email', 'testuser@example.com')->first();

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id'        => $user->id,
            'correct_answers' => 4,
        ]);
    }

    /** @test */
    public function it_does_not_sync_already_migrated_guest_device(): void
    {
        $existingUser = User::factory()->create();
        // Device already migrated (user_id is set)
        $device = $this->makeGuestDevice(['user_id' => $existingUser->id]);

        $response = $this->postJson('/api/register', $this->validPayload([
            'device_id'         => $device->device_id,
            'token'             => $device->token,
            'guest_fingerprint' => $device->guest_fingerprint,
        ]));

        $response->assertStatus(201)
            // guest_synced must be false because the device is already migrated
            ->assertJsonPath('data.guest_synced', false);
    }

    /** @test */
    public function it_completes_registration_even_when_guest_sync_throws(): void
    {
        $device = $this->makeGuestDevice();

        // Force GuestDeviceSyncService::sync() to throw
        $this->mock(GuestDeviceSyncService::class, function ($mock) {
            $mock->shouldReceive('sync')
                ->once()
                ->andThrow(new GuestDeviceSyncException('Simulated sync failure'));
        });

        Log::shouldReceive('error')->atLeast()->once();

        $response = $this->postJson('/api/register', $this->validPayload([
            'device_id'         => $device->device_id,
            'token'             => $device->token,
            'guest_fingerprint' => $device->guest_fingerprint,
        ]));

        // Registration must still succeed (201) even though sync failed
        $response->assertStatus(201)
            ->assertJsonPath('data.guest_synced', false);

        // User must exist in DB
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    /** @test */
    public function it_accepts_guest_device_credentials_from_headers(): void
    {
        $device = $this->makeGuestDevice();

        $response = $this->withHeaders([
            'X-Guest-Device-Id'   => $device->device_id,
            'X-Guest-Token'       => $device->token,
            'X-Guest-Fingerprint' => $device->guest_fingerprint,
        ])->postJson('/api/register', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonPath('data.guest_synced', true);
    }

    /* ------------------------------------------------------------------
     * Fingerprint fallback sync
     * ----------------------------------------------------------------*/

    /** @test */
    public function it_syncs_attempts_by_guest_device_id(): void
    {
        $device = $this->makeGuestDevice();

        $stage = \App\Models\EarTrainingStage::firstOrCreate(['title' => 'Test Stage'], ['sort_order' => 1]);
        $level = EarTrainingLevel::create(['ear_training_stage_id' => $stage->id, 'name' => 'Intermediate', 'level_number' => 2, 'is_premium' => false]);

        // Attempt linked by guest_device_id (the standard flow)
        \App\Models\EarTrainingUserProgress::create([
            'guest_device_id'       => $device->id,
            'ear_training_level_id' => $level->id,
            'is_completed'          => true,
            'best_accuracy_percentage' => 60.00,
        ]);

        $response = $this->postJson('/api/register', $this->validPayload([
            'device_id'         => $device->device_id,
            'token'             => $device->token,
            'guest_fingerprint' => $device->guest_fingerprint,
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.guest_synced', true);

        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertDatabaseHas('ear_training_user_progress', [
            'user_id'              => $user->id,
            'ear_training_level_id' => $level->id,
        ]);
    }
}
