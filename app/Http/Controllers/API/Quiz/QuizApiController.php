<?php

namespace App\Http\Controllers\API\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\QuizCheckAnswerRequest;
use App\Http\Requests\Quiz\QuizSubmitRequest;
use App\Http\Resources\Quiz\QuizReviewResource;
use App\Models\GuestDevice;
use App\Services\QuizService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API controller for quiz operations.
 */
class QuizApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private QuizService $quizService
    ) {}

    /**
     * GET /api/quiz/by-learning/{learningId}
     */
    public function byLearning($learningId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->quizService->getQuizByLearning((int) $learningId, $user, $guestDevice);

            return $this->success('Quiz fetched successfully.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Quiz not found.', 404);
        }
    }

    /**
     * POST /api/quiz/check-answer
     */
    public function checkAnswer(QuizCheckAnswerRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $data = $this->quizService->checkAnswer(
                (int) $validated['question_id'],
                $validated['answer']
            );

            return $this->success($data['is_correct'] ? 'Correct!' : 'Incorrect.', $data);
        } catch (\Throwable $e) {
            return $this->error('Answer check failed.', 400);
        }
    }

    /**
     * POST /api/quiz/submit/{learningId}
     */
    public function submit(QuizSubmitRequest $request, $learningId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $data = $this->quizService->submitQuiz(
                (int) $learningId,
                $request->validated(),
                $user,
                $request
            );

            return $this->success('Quiz submitted successfully.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Quiz submission failed.', 400);
        }
    }

    /**
     * GET /api/quiz/review/{attemptId}
     */
    public function review($attemptId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->quizService->getReview((int) $attemptId, $user, $guestDevice);

            $resource = new QuizReviewResource($data);

            return $this->success('Review answers fetched successfully.', $resource->toArray(request()));
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Review not found.', 404);
        }
    }

    /**
     * GET /api/quiz/review-by-learning/{learningId}
     */
    public function reviewByLearning($learningId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->quizService->getReviewByLearning((int) $learningId, $user, $guestDevice);

            $resource = new QuizReviewResource($data);

            return $this->success('Review answers fetched successfully.', $resource->toArray(request()));
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Review not found.', 404);
        }
    }

    /**
     * POST /api/quiz/retry/{learningId}
     */
    public function retry($learningId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->quizService->retryQuiz((int) $learningId, $user, $guestDevice);

            return $this->success('Quiz fetched successfully.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Retry failed.', 400);
        }
    }

    /**
     * GET /api/quiz/attempt-details/{id}
     */
    public function attemptDetails($id): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $guestDevice = $user ? null : $this->getGuestDevice(request());

            $data = $this->quizService->getAttemptDetails((int) $id, $user, $guestDevice);

            return $this->success('Quiz attempt details fetched successfully.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Nothing found.', 404);
        }
    }

    /**
     * GET /api/quiz/questions
     */
    public function questions(): JsonResponse
    {
        try {
            $data = $this->quizService->getAllQuestions();

            return $this->success('Quiz questions fetched successfully.', $data);
        } catch (\Throwable $e) {
            return $this->error('Questions not found.', 404);
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
