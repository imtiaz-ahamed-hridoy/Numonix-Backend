<?php

namespace App\Channels;

use App\Services\FCMService;
use Illuminate\Notifications\Notification;

class FirebaseChannel
{
    protected FCMService $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function send($notifiable, Notification $notification)
    {
        $pushEnabled = $notifiable->notificationSettings 
            ? $notifiable->notificationSettings->push_notifications 
            : true;

        if (!$pushEnabled) {
            return;
        }

        if (!method_exists($notification, 'toFirebase')) {
            return;
        }

        /** @var \AppNotificationsAppNotification $notification */
        $message = $notification->toFirebase($notifiable);

        if (!$message || !isset($message['title'], $message['body'])) {
            return;
        }

        $tokens = $notifiable->firebaseTokens()->pluck('token')->filter()->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->fcmService->sendMulticast(
            $tokens,
            $message['title'],
            $message['body'],
            $message['data'] ?? []
        );
    }
}
