<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
     * Event dispatched when a user successfully verifies their email address.
     */
    class EmailVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The authenticated user
     */
    public User $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
