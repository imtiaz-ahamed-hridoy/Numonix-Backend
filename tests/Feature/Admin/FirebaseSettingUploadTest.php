<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FirebaseSettingUploadTest extends TestCase
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

    protected function tearDown(): void
    {
        $cleanup = [
            storage_path('app/firebase-credentials.json'),
            storage_path('app/private/firebase-credentials.json'),
            storage_path('firebase-credentials.json'),
        ];
        foreach ($cleanup as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

    public function test_guest_cannot_access_firebase_settings(): void
    {
        $response = $this->get(route('firebase.setting'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_firebase_settings(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('firebase.setting'));
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_admin_can_view_firebase_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('firebase.setting'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.layouts.settings.firebase_settings');
        $response->assertViewHas('activeConfig');
    }

    public function test_admin_can_upload_valid_firebase_credentials(): void
    {
        $fakeServiceAccount = [
            'type' => 'service_account',
            'project_id' => 'test-project',
            'private_key_id' => 'key123',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC...\n-----END PRIVATE KEY-----\n",
            'client_email' => 'firebase-adminsdk@test-project.iam.gserviceaccount.com',
            'client_id' => 'client123',
        ];

        $file = UploadedFile::fake()->createWithContent(
            'firebase-credentials.json',
            json_encode($fakeServiceAccount)
        );

        $response = $this->actingAs($this->admin)->post(route('firebase.setting.update'), [
            'firebase_credentials' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('t-success');

        $this->assertTrue(File::exists(storage_path('app/private/firebase-credentials.json')));
        $savedContent = json_decode(File::get(storage_path('app/private/firebase-credentials.json')), true);
        $this->assertEquals('test-project', $savedContent['project_id']);
    }

    public function test_upload_rejects_invalid_json(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'not-json.json',
            'This is not valid json content'
        );

        $response = $this->actingAs($this->admin)->post(route('firebase.setting.update'), [
            'firebase_credentials' => $file,
        ]);

        $response->assertSessionHasErrors(['firebase_credentials']);
    }

    public function test_upload_rejects_json_without_service_account_keys(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'missing-keys.json',
            json_encode(['random_key' => 'random_value'])
        );

        $response = $this->actingAs($this->admin)->post(route('firebase.setting.update'), [
            'firebase_credentials' => $file,
        ]);

        $response->assertSessionHasErrors(['firebase_credentials']);
    }

    public function test_admin_can_delete_firebase_credentials(): void
    {
        // First put a file in storage
        File::put(storage_path('app/firebase-credentials.json'), json_encode([
            'project_id' => 'to-be-deleted',
            'private_key' => 'key',
        ]));
        $this->assertTrue(File::exists(storage_path('app/firebase-credentials.json')));

        $response = $this->actingAs($this->admin)->delete(route('firebase.setting.destroy'));
        $response->assertRedirect();
        $response->assertSessionHas('t-success');

        $this->assertFalse(File::exists(storage_path('app/firebase-credentials.json')));
    }
}
