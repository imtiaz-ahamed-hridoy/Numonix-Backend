<?php

namespace App\Http\Controllers\API\Preferences;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Preferences\StorePreferencesRequest;
use App\Services\PreferencesService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * API controller for user preferences.
 */
class PreferencesController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PreferencesService $preferencesService
    ) {}

    /**
     * Get all preferences for the authenticated user.
     */
public function index(): JsonResponse
    {
        $user = Auth::user();
        $preferences = $this->preferencesService->getPreferences($user->id);

        return $this->success('Preferences fetched successfully.', [
            'preferences' => $preferences,
        ]);
    }

    /**
     * Store or update preferences for the authenticated user.
     */
public function store(StorePreferencesRequest $request): JsonResponse
    {
        $user = Auth::user();
        $preferences = $this->preferencesService->updatePreferences(
            $user->id,
            $request->validated()['preferences']
        );

        return $this->success('Preferences saved successfully.', [
            'preferences' => $preferences,
        ]);
    }
}
