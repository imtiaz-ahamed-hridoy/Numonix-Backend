<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Laravel\Paddle\Events\WebhookReceived;
use App\Listeners\PaddleWebhookListener;
use Laravel\Paddle\Cashier;
use App\Services\FirebaseCredentialResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings are now centralized in RepositoryServiceProvider
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Disable Cashier's auto-registered /paddle/webhook route.
        // We register it manually in routes/web.php with our payload-validation
        // middleware prepended (prevents 500 on malformed/empty payloads).
        Cashier::ignoreRoutes();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Event::listen(WebhookReceived::class, PaddleWebhookListener::class);

        // Fix SSL certificate mismatch on shared hosting SMTP servers
        // The mail host certificate CN may not match the hostname (common on cPanel shared hosting)
        Mail::extend('smtp', function (array $config) {
            $scheme = $config['scheme'] ?? null;

            if (! $scheme) {
                $scheme = ($config['port'] == 465) ? 'smtps' : 'smtp';
            }

            $factory = new EsmtpTransportFactory;
            $transport = $factory->create(new Dsn(
                $scheme,
                $config['host'],
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['port'] ?? null,
                $config
            ));

            $stream = $transport->getStream();
            if ($stream instanceof SocketStream) {
                $stream->setStreamOptions([
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                    ],
                ]);
            }

            return $transport;
        });

        $this->bootFirebaseCredentials();

        // Register Apple provider for Laravel Socialite
        \Laravel\Socialite\Facades\Socialite::extend('apple', function ($app) {
            $config = $app['config']['services.apple'] ?? [];
            return new \App\Services\Socialite\AppleProvider(
                $app['request'],
                $config['client_id'] ?? config('services.apple.client_id', 'com.example.app'),
                $config['client_secret'] ?? '',
                $config['redirect'] ?? ''
            );
        });
    }

    /**
     * Dynamically resolve Firebase credentials from dashboard uploads, direct env, or Google credentials.
     */
    protected function bootFirebaseCredentials(): void
    {
        $resolved = FirebaseCredentialResolver::resolve();
        config(['firebase.projects.app.credentials' => $resolved]);
        config(['services.firebase.credentials_file' => $resolved]);
    }
}

