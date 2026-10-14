<?php

namespace App\Jobs;

use App\Mail\UserDataExportMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function handle(): void
    {
        $user = $this->user;

        // Collect all user-related data for export
        $payload = [
            'personal_information' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob'   => optional($user->dob)->format('Y-m-d'),
            ],
            'notification_settings' => $user->notificationSettings?->only([
                'email_notifications', 'push_notifications',
                'lesson_updates', 'practice_reminders',
            ]),
            'cookie_settings' => $user->cookieSetting?->only([
                'essential_cookies', 'analytics_cookies',
            ]),
            'notifications' => $user->notifications()->latest()->get()->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ])->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        $json     = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $filename = 'exports/user-data-' . $user->id . '-' . now()->format('YmdHis') . '.json';

        Storage::disk('local')->put($filename, $json);

        Mail::to($user->email)->send(new UserDataExportMail($user, $filename));

        // Clean up the file after sending
        Storage::disk('local')->delete($filename);
    }
}
