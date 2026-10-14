<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;
use App\Repositories\Interfaces\UserRepositoryInterface;

/**
 * Service for managing user notification retrieval and state.
 */
class NotificationService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all notifications for a user, grouped by date.
     *
     * @param  User  $user
     * @return array{unread_count: int, recent: array}
     */
    public function getNotifications($user): array
    {
        $notifications = $user->notifications()->latest()->get();

        $grouped = $notifications->groupBy(function ($notification) {
            return $notification->created_at->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'date' => $date,
                'label' => $this->getDateLabel($date),
                'items' => $items->map(function ($item) {
                    $payload = $item->data ?? [];

                    return [
                        'id' => (string) $item->id,
                        'title' => $payload['title'] ?? '',
                        'body' => $payload['message'] ?? $payload['body'] ?? '',
                        'type' => $payload['type'] ?? 'general',
                        'data' => $payload['data'] ?? [],
                        'read_at' => $item->read_at,
                        'is_read' => ! is_null($item->read_at),
                        'created_at' => $item->created_at,
                        'created_at_human' => $item->created_at->diffForHumans(),
                    ];
                }),
            ];
        })->values();

        $unreadCount = $notifications->whereNull('read_at')->count();

        return [
            'unread_count' => $unreadCount,
            'recent' => $grouped,
        ];
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($user, int|string $id): void
    {
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead($user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Send a custom notification from an admin to a specific user or all users.
     */
    public function sendCustom($sender, array $validated): array
    {
        $recipientType = $validated['recipient_type'] ?? (isset($validated['user_ids']) || isset($validated['user_id']) ? 'specific' : 'all');

        if ($recipientType === 'all') {
            $users = User::query()->where('status', 'active')->where('role', 'User')->get();
            if ($users->isEmpty()) {
                $users = User::query()->where('status', 'active')->get();
            }
        } else {
            $userIds = $validated['user_ids'] ?? (isset($validated['user_id']) ? [(int) $validated['user_id']] : []);
            $users = User::query()->whereIn('id', $userIds)->get();
        }

        $sentCount = 0;
        foreach ($users as $user) {
            $user->notify(new AppNotification(
                $validated['title'],
                $validated['body'],
                $validated['data'] ?? [],
                'custom_notification'
            ));
            $sentCount++;
        }

        return [
            'sent_count' => $sentCount,
            'recipient_type' => $recipientType,
        ];
    }

    private function getDateLabel(string $date): string
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        if ($date === $today) {
            return 'RECENT';
        } elseif ($date === $yesterday) {
            return 'YESTERDAY';
        }

        return date('F j, Y', strtotime($date));
    }
}