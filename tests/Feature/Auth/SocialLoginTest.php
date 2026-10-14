<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\SocialLoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_login_rejects_unsupported_provider(): void
    {
        $response = $this->postJson('/api/social/login', [
            'provider' => 'twitter',
            'token'    => 'dummy-token',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['provider']);
    }

    public function test_social_login_requires_token(): void
    {
        $response = $this->postJson('/api/social/login', [
            'provider' => 'google',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token']);
    }

    public function test_social_login_google_creates_user_and_returns_token(): void
    {
        $socialiteUser = (new SocialiteUser)->setRaw([
            'sub' => 'google-123456789',
            'email' => 'googleuser@example.com',
            'name' => 'Google Test User',
            'picture' => 'https://example.com/avatar.jpg',
        ])->map([
            'id' => 'google-123456789',
            'email' => 'googleuser@example.com',
            'name' => 'Google Test User',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $providerMock = \Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('userFromToken')
            ->with('google-valid-token')
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($providerMock);

        $response = $this->postJson('/api/social/login', [
            'provider' => 'google',
            'token'    => 'google-valid-token',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'token_type',
                'is_new_account',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);

        $this->assertTrue($response->json('data.is_new_account'));
        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'socialite_provider' => 'google',
            'socialite_id' => 'google-123456789',
        ]);
    }

    public function test_social_login_google_existing_user_logs_in(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'name'  => 'Existing User',
        ]);

        $socialiteUser = (new SocialiteUser)->setRaw([
            'sub' => 'google-987654321',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ])->map([
            'id' => 'google-987654321',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'avatar' => null,
        ]);

        $providerMock = \Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('userFromToken')
            ->with('google-existing-token')
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($providerMock);

        $response = $this->postJson('/api/social/login', [
            'provider' => 'google',
            'token'    => 'google-existing-token',
        ]);

        $response->assertStatus(200);
        $this->assertEquals($existing->id, $response->json('data.user.id'));
        $this->assertFalse($response->json('data.is_new_account'));
    }

    public function test_social_login_apple_creates_user_and_returns_token(): void
    {
        $appleSocialiteUser = (new SocialiteUser)->setRaw([
            'sub' => 'apple-001234.abcdef',
            'email' => 'appleuser@example.com',
            'name' => 'Apple User',
        ])->map([
            'id' => 'apple-001234.abcdef',
            'email' => 'appleuser@example.com',
            'name' => 'Apple User',
            'avatar' => null,
        ]);

        $appleProviderMock = \Mockery::mock('App\Services\Socialite\AppleProvider');
        $appleProviderMock->shouldReceive('stateless')->andReturnSelf();
        $appleProviderMock->shouldReceive('userFromToken')
            ->with('apple-valid-token')
            ->andReturn($appleSocialiteUser);

        Socialite::shouldReceive('driver')
            ->with('apple')
            ->andReturn($appleProviderMock);

        $response = $this->postJson('/api/social/login', [
            'provider' => 'apple',
            'token'    => 'apple-valid-token',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.is_new_account'));
        $this->assertDatabaseHas('users', [
            'email' => 'appleuser@example.com',
            'socialite_provider' => 'apple',
            'socialite_id' => 'apple-001234.abcdef',
        ]);
    }

    public function test_social_login_apple_subsequent_login_without_email_resolves_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'appleuser@example.com',
            'socialite_provider' => 'apple',
            'socialite_id' => 'apple-001234.abcdef',
        ]);

        // Apple token without email (common on 2nd+ logins)
        $appleSocialiteUser = (new SocialiteUser)->setRaw([
            'sub' => 'apple-001234.abcdef',
            'email' => null,
            'name' => 'Apple User',
        ])->map([
            'id' => 'apple-001234.abcdef',
            'email' => null,
            'name' => 'Apple User',
            'avatar' => null,
        ]);

        $appleProviderMock = \Mockery::mock('App\Services\Socialite\AppleProvider');
        $appleProviderMock->shouldReceive('stateless')->andReturnSelf();
        $appleProviderMock->shouldReceive('userFromToken')
            ->with('apple-subsequent-token')
            ->andReturn($appleSocialiteUser);

        Socialite::shouldReceive('driver')
            ->with('apple')
            ->andReturn($appleProviderMock);

        $response = $this->postJson('/api/social/login', [
            'provider' => 'apple',
            'token'    => 'apple-subsequent-token',
        ]);

        $response->assertStatus(200);
        $this->assertEquals($user->id, $response->json('data.user.id'));
        $this->assertFalse($response->json('data.is_new_account'));
    }

    public function test_social_login_apple_new_user_with_client_supplied_email(): void
    {
        // Token has no email, but client supplied it in the request
        $appleSocialiteUser = (new SocialiteUser)->setRaw([
            'sub' => 'apple-999999.clientemail',
            'email' => null,
            'name' => 'Apple User',
        ])->map([
            'id' => 'apple-999999.clientemail',
            'email' => null,
            'name' => 'Apple User',
            'avatar' => null,
        ]);

        $appleProviderMock = \Mockery::mock('App\Services\Socialite\AppleProvider');
        $appleProviderMock->shouldReceive('stateless')->andReturnSelf();
        $appleProviderMock->shouldReceive('userFromToken')
            ->with('apple-client-email-token')
            ->andReturn($appleSocialiteUser);

        Socialite::shouldReceive('driver')
            ->with('apple')
            ->andReturn($appleProviderMock);

        $response = $this->postJson('/api/social/login', [
            'provider' => 'apple',
            'token'    => 'apple-client-email-token',
            'email'    => 'supplied_by_apple_sheet@example.com',
            'name'     => 'John Doe',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.is_new_account'));
        $this->assertDatabaseHas('users', [
            'email' => 'supplied_by_apple_sheet@example.com',
            'name'  => 'John Doe',
            'socialite_provider' => 'apple',
            'socialite_id' => 'apple-999999.clientemail',
        ]);
    }

    public function test_social_login_invalid_token_returns_401(): void
    {
        $response = $this->postJson('/api/social/login', [
            'provider' => 'google',
            'token'    => 'invalid-garbage-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 401,
        ]);
    }

    public function test_apple_redirect_callback_returns_ok(): void
    {
        $response = $this->postJson('/api/social/apple/callback');
        $response->assertStatus(200);
    }
}
