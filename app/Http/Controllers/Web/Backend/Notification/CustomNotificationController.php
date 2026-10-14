<?php

namespace App\Http\Controllers\Web\Backend\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Backend\Notification\SendCustomNotificationRequest;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class CustomNotificationController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('role', 'User')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $notifications = DatabaseNotification::query()
            ->latest()
            ->limit(50)
            ->get()
            ->filter(fn (DatabaseNotification $notification) => ($notification->data['type'] ?? null) === 'custom_notification');

        return view('backend.layouts.notification.custom', compact('users', 'notifications'));
    }

    public function send(SendCustomNotificationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $query = User::query()
            ->where('role', 'User')
            ->where('status', 'active');

        if ($validated['recipient_type'] === 'specific') {
            $query->whereIn('id', $validated['user_ids']);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return back()->withInput()->with('t-error', 'No eligible users found for this notification.');
        }

        $data = [];
        if (! empty($validated['data'])) {
            $data = json_decode($validated['data'], true) ?: [];
        }

        $sentCount = 0;

        foreach ($users as $user) {
            $user->notify(new AppNotification(
                $validated['title'],
                $validated['body'],
                $data,
                'custom_notification'
            ));

            $sentCount++;
        }

        return redirect()
            ->route('custom-notifications.index')
            ->with('t-success', "Notification sent to {$sentCount} user(s).");
    }
}
