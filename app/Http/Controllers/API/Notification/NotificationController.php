<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\SendCustomNotificationRequest;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for sending custom notifications.
 */
class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->notificationService->getNotifications($request->user());

        return $this->success('Notifications fetched successfully.', $data);
    }

    public function markAsRead($id): JsonResponse
    {
        $this->notificationService->markAsRead(request()->user(), (string) $id);

        return $this->success('Notification marked as read.', ['id' => (string) $id]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $this->notificationService->markAllAsRead(request()->user());

        return $this->success('All notifications marked as read.');
    }

    /**
     * Send a custom notification to specified users.
     */
public function sendCustom(SendCustomNotificationRequest $request): JsonResponse
    {
        if ($request->user()?->role !== 'Admin') {
            return $this->error('Only an admin can send custom notifications.', 403);
        }

        $notification = $this->notificationService->sendCustom($request->user(), $request->validated());

        return $this->success('Notification sent successfully.', $notification);
    }
}
