<?php

namespace App\Notifications;

use App\Channels\FirebaseChannel;
use Illuminate\Notifications\Notification;

/**
     * Notification sent via database and optionally Firebase push channels.
     */
    class AppNotification extends Notification
{

    public $title;
    public $body;
    public $data;
    public $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $body, array $data = [], string $type = 'general')
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Add firebase channel if the user has push notifications enabled in settings
        // If settings are not yet created (null), default to true.
        $pushEnabled = $notifiable->notificationSettings 
            ? $notifiable->notificationSettings->push_notifications 
            : true;

        if ($pushEnabled) {
            $channels[] = FirebaseChannel::class;
        }

        return $channels;
    }

    /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'data'  => array_merge(['type' => $this->type], $this->data),
        ];
    }

    /**
     * Get the array representation of the notification for the database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->body,
            'type'    => $this->type,
            'data'    => $this->data,
        ];
    }
}
