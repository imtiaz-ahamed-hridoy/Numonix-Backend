<?php

namespace App\Http\Controllers\API\EarTraining;

use App\Http\Controllers\Controller;
use App\Models\GuestDevice;
use App\Services\EarTrainingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * API controller for ear training operations.
 */
class EarTrainingApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private EarTrainingService $earTrainingService
    ) {}

    /**
     * GET /api/ear-training/stages
     */
    public function stages(): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->earTrainingService->getStages($user, $guestDevice);

            return $this->success('Ear training levels fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Stages not found. ' . $e->getMessage(), 404);
        }
    }

    /**
     * GET /api/ear-training/stages/{stageId}/levels
     */
    public function levels($stageId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->earTrainingService->getLevelsForStage((int)$stageId, $user, $guestDevice);

            return $this->success('Level quizzes fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Levels not found. ' . $e->getMessage(), 404);
        }
    }

    /**
     * POST /api/ear-training/submit/{levelId}
     */
    public function submit(Request $request, $levelId): JsonResponse
    {
        $validated = $request->validate([
            'correct_answers' => 'required|integer|min:0',
            'total_exercises' => 'required|integer|min:1',
            'answers' => 'nullable|array',
            'answers.*.user_answer' => 'required',
            'answers.*.correct_answer' => 'required',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        try {
            $user = auth('sanctum')->user();

            $data = $this->earTrainingService->submitLevel(
                (int) $levelId,
                $validated,
                $user,
                $request
            );

            return $this->success('Ear training level submitted successfully.', $data);
        } catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getHeaders());
        } catch (ModelNotFoundException $e) {
            return $this->error('Level not found.', 404);
        } catch (\Throwable $e) {
            return $this->error('Level submission failed: ' . $e->getMessage(), 400);
        }
    }

    /**
     * GET /api/ear-training/explanation-templates
     */
    public function explanationTemplates(): JsonResponse
    {
        try {
            $templates = \App\Models\EarTrainingExplanationTemplate::all();
            
            $formatted = $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'type' => $template->type,
                    'from_function' => $template->from_function,
                    'to_function' => $template->to_function,
                    'phrase' => $template->phrase,
                    'pattern' => $template->pattern,
                    'title' => $template->title,
                    'body' => $template->body,
                    'hint_text' => $template->hint_text,
                    'display_value' => $template->display_value,
                    'exercise_type' => $template->exercise_type,
                    'prompt_text' => $template->prompt_text,
                ];
            });

            return $this->success('Explanation templates fetched successfully.', $formatted);
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch explanation templates. ' . $e->getMessage(), 500);
        }
    }

    /* ------------------------------------------------------------------
     * Private Helpers
     * ----------------------------------------------------------------*/

    private function getGuestDevice(Request $request): ?GuestDevice
    {
        return $request->input('_guest_device');
    }
}
