<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\UpdateNotificationSettingsRequest;
use App\Services\NotificationSettingsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for notification preferences.
 */
class NotificationSettingsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NotificationSettingsService $notificationSettingsService
    ) {}

    /**
     * Get the notification settings for the authenticated user.
     */
public function show(Request $request): JsonResponse
    {
        $data = $this->notificationSettingsService->showSettings($request->user());

        return $this->success('Notification settings retrieved successfully.', $data);
    }

    /**
     * Update the notification settings for the authenticated user.
     */
public function update(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        $data = $this->notificationSettingsService->updateSettings($request->user(), $request->validated());

        return $this->success('Notification settings updated successfully.', $data);
    }
}
