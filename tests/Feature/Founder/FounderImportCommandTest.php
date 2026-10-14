<?php

namespace Tests\Feature\Founder;

use App\Models\User;
use App\Mail\FounderWelcomeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FounderImportCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $tempCsvPath;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->tempCsvPath = storage_path('app/temp_test_founders.csv');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempCsvPath)) {
            File::delete($this->tempCsvPath);
        }
        parent::tearDown();
    }

    public function test_import_creates_founders_with_tier_2_and_30_day_trial(): void
    {
        $csvContent = "name,email\nAlice Smith,alice@example.com\nBob Jones,bob@example.com\n";
        File::put($this->tempCsvPath, $csvContent);

        $this->artisan('founders:import', ['file' => $this->tempCsvPath])
            ->assertExitCode(0);

        $alice = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($alice);
        $this->assertEquals('Alice Smith', $alice->name);
        $this->assertTrue($alice->is_founder);
        $this->assertNotNull($alice->activation_token);

        // Activate via single-use token (Launch Roadmap 7.4 rolling activation)
        $response = $this->getJson('/api/founder/activate/' . $alice->activation_token);
        $response->assertStatus(200);

        $alice->refresh();
        $this->assertEquals('tier_3', $alice->subscription_tier);
        $this->assertTrue($alice->is_subscribed);
        $this->assertNotNull($alice->founder_trial_ends_at);
        $this->assertTrue($alice->founder_trial_ends_at->isFuture());

        Mail::assertSent(FounderWelcomeEmail::class, 2);
    }

    public function test_import_is_idempotent_and_skips_existing_founders(): void
    {
        $csvContent = "name,email\nCharlie,charlie@example.com\n";
        File::put($this->tempCsvPath, $csvContent);

        // Run once
        $this->artisan('founders:import', ['file' => $this->tempCsvPath])
            ->assertExitCode(0);
        $this->assertDatabaseHas('users', ['email' => 'charlie@example.com']);

        // Run again
        $this->artisan('founders:import', ['file' => $this->tempCsvPath])
            ->assertExitCode(0);

        // Only 1 welcome email should have been sent
        Mail::assertSent(FounderWelcomeEmail::class, 1);
    }
}
