<?php

namespace Tests\Feature\Founder;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FounderDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'Admin',
        ]);
    }

    public function test_guest_cannot_access_founder_dashboard(): void
    {
        $response = $this->get(route('founders.index'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_founder_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'User']);

        $response = $this->actingAs($user)->get(route('founders.index'));
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    public function test_admin_can_access_founder_dashboard(): void
    {
        User::factory()->create([
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->addDays(10),
            'founder_review_completed' => false,
            'founder_converted_at' => null,
        ]);

        User::factory()->create([
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->subDays(5),
            'founder_review_completed' => true,
            'founder_converted_at' => Carbon::now()->subDays(2),
        ]);

        $response = $this->actingAs($this->admin)->get(route('founders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.founder.index');
        $response->assertViewHas('stats');
        $response->assertViewHas('founders');

        $stats = $response->viewData('stats');
        $this->assertEquals(2, $stats['totalFounders']);
        $this->assertEquals(1, $stats['activeTrials']);
        $this->assertEquals(1, $stats['converted']);
    }

    public function test_admin_can_filter_founders_by_status(): void
    {
        User::factory()->create([
            'name' => 'Active Trial Founder',
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->addDays(10),
            'founder_converted_at' => null,
        ]);

        User::factory()->create([
            'name' => 'Converted Founder',
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->subDays(2),
            'founder_converted_at' => Carbon::now()->subDays(1),
        ]);

        $responseActive = $this->actingAs($this->admin)->get(route('founders.index', ['status' => 'active']));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Active Trial Founder');
        $responseActive->assertDontSee('Converted Founder');

        $responseConverted = $this->actingAs($this->admin)->get(route('founders.index', ['status' => 'converted']));
        $responseConverted->assertStatus(200);
        $responseConverted->assertSee('Converted Founder');
        $responseConverted->assertDontSee('Active Trial Founder');
    }

    public function test_admin_can_export_founders_csv(): void
    {
        User::factory()->create([
            'name' => 'Exportable Founder',
            'email' => 'exportable@founder.test',
            'is_founder' => true,
            'founder_trial_ends_at' => Carbon::now()->addDays(15),
        ]);

        $response = $this->actingAs($this->admin)->get(route('founders.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'attachment; filename="founders-export-'));
    }
}
