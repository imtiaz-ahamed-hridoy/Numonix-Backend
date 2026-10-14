<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardFullTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'   => 'Admin',
            'status' => 'Active',
        ]);

        $this->regularUser = User::factory()->create([
            'role'   => 'User',
            'status' => 'Active',
        ]);
    }

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/dashboard');
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_admin_can_access_main_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.layouts.dashboard.index');
    }

    public function test_admin_can_access_users_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('user.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_founders_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('founders.index'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.founder.index');
    }

    public function test_admin_can_access_learning_cms(): void
    {
        $response = $this->actingAs($this->admin)->get(route('learning.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_learning_groups(): void
    {
        $response = $this->actingAs($this->admin)->get(route('learning-group.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_home_modules(): void
    {
        $response = $this->actingAs($this->admin)->get(route('home-module.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_quiz_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quiz.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_ear_training_stages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ear-training.stages.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_ear_training_templates(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ear-training.templates.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_faqs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('faq.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_reviews(): void
    {
        $response = $this->actingAs($this->admin)->get(route('review.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_custom_notifications(): void
    {
        $response = $this->actingAs($this->admin)->get(route('custom-notifications.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_activity_logs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('activity.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_guest_access_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('guest-access.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_all_cms_pages(): void
    {
        $cmsRoutes = [
            'cms.user-guide.index',
            'cms.terms-conditions.index',
            'cms.privacy-policy.index',
            'cms.glossary.index',
            'cms.complete-glossary.index',
            'cms.about-numonix.index',
        ];

        foreach ($cmsRoutes as $routeName) {
            $response = $this->actingAs($this->admin)->get(route($routeName));
            $response->assertStatus(200);
        }
    }
}
