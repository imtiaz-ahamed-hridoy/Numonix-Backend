<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_notifications(): void
    {
        $user = User::factory()->create(['role' => 'User', 'status' => 'active']);
        $user->notify(new AppNotification('Test Notification', 'Body of test notification', ['key' => 'val'], 'lessonUnlocked'));

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('data.unread_count', 1);

        $this->assertNotEmpty($response->json('data.recent'));
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'User', 'status' => 'active']);
        $user->notify(new AppNotification('Test Notification', 'Body', [], 'lessonUnlocked'));

        $notification = $user->notifications()->first();
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/notifications/{$notification->id}/mark-as-read");

        $response->assertStatus(200);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'User', 'status' => 'active']);
        $user->notify(new AppNotification('Note 1', 'Body 1', [], 'general'));
        $user->notify(new AppNotification('Note 2', 'Body 2', [], 'general'));

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/notifications/mark-all-as-read');

        $response->assertStatus(200);
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_non_admin_cannot_send_custom_notification(): void
    {
        $user = User::factory()->create(['role' => 'User', 'status' => 'active']);
        $target = User::factory()->create(['role' => 'User', 'status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/notifications/send', [
            'user_id' => $target->id,
            'title' => 'Unauthorized Broadcast',
            'body' => 'Should fail with 403',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_send_custom_notification_to_specific_user(): void
    {
        $admin = User::factory()->create(['role' => 'Admin', 'status' => 'active']);
        $target = User::factory()->create(['role' => 'User', 'status' => 'active']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/notifications/send', [
            'recipient_type' => 'specific',
            'user_ids' => [$target->id],
            'title' => 'Admin Announcement',
            'body' => 'Important update for user',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sent_count', 1);

        $this->assertEquals(1, $target->notifications()->count());
        $this->assertEquals('Admin Announcement', $target->notifications()->first()->data['title']);
    }

    public function test_admin_can_send_custom_notification_to_all_users(): void
    {
        $admin = User::factory()->create(['role' => 'Admin', 'status' => 'active']);
        User::factory()->count(3)->create(['role' => 'User', 'status' => 'active']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/notifications/send', [
            'recipient_type' => 'all',
            'title' => 'Broadcast To All',
            'body' => 'Global notification',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sent_count', 3);
    }

    public function test_user_can_add_and_delete_firebase_token(): void
    {
        $user = User::factory()->create(['role' => 'User', 'status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/firebase-token/add', [
            'token' => 'fcm_test_token_12345',
            'device_id' => 'device_test_123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('firebase_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm_test_token_12345',
            'device_id' => 'device_test_123',
        ]);

        $delResponse = $this->actingAs($user, 'sanctum')->postJson('/api/firebase-token/delete', [
            'token' => 'fcm_test_token_12345',
            'device_id' => 'device_test_123',
        ]);

        $delResponse->assertStatus(200);
        $this->assertDatabaseMissing('firebase_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm_test_token_12345',
            'device_id' => 'device_test_123',
        ]);
    }
}