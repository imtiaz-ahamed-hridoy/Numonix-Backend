<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\CompleteLearningRequest;
use App\Http\Requests\Learning\FetchLearningRequest;
use App\Http\Requests\Learning\LearningNavigationRequest;
use App\Http\Requests\Learning\ShowLearningRequest;
use App\Http\Requests\Learning\UpdateChecklistRequest;
use App\Http\Resources\Learning\LearningSummaryResource;
use App\Services\LearningService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API controller for learning/lesson operations.
 *
 * Handles learning listing (by category, grouped, filtered),
 * detail retrieval with paginated pages, summary,
 * completion tracking, checklist updates, and navigation
 * to next/previous learnings.
 */
class LearningController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LearningService $learningService
    ) {}

    /**
     * GET /api/v1/learnings
     */
    public function index(FetchLearningRequest $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $category = $request->validated()['category'] ?? null;

            $result = $this->learningService->getAllLearnings($category, $user, $request);

            return $this->success('Learnings fetched successfully.', $result);
        } catch (\Throwable $e) {
            return $this->error('Learnings not found.', 404);
        }
    }

    /**
     * GET /api/v1/learnings/lessons
     */
    public function lessons(): JsonResponse
    {
        return $this->fetchByCategory('lesson');
    }

    /**
     * GET /api/v1/learnings/core-theory
     */
    public function coreTheory(): JsonResponse
    {
        return $this->fetchByCategory('core_theory');
    }

    /**
     * GET /api/v1/learnings/advanced-insights
     */
    public function advancedInsights(): JsonResponse
    {
        return $this->fetchByCategory('advanced_insight');
    }

    /**
     * GET /api/v1/learnings/case-studies
     */
    public function caseStudies(): JsonResponse
    {
        return $this->fetchGroupedCategory('case_study', compact: true, stripGroupMeta: true);
    }

    /**
     * GET /api/v1/learnings/fnh-architecture
     */
    public function fnhArchitecture(): JsonResponse
    {
        return $this->fetchGroupedCategory('fnh_architecture', compact: true, stripGroupMeta: true);
    }

    /**
     * GET /api/v1/learnings/show/{id}
     */
    public function show(int $id, ShowLearningRequest $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            $pageNum = $request->integer('page', 0);
            $perPage = $request->integer('per_page', 0);

            $data = $this->learningService->getLearningDetail($id, $user, $pageNum, $perPage, $request);

            return $this->success('Learning fetched successfully.', [
                'learning' => $data['learning'],
                'quiz' => $data['quiz'],
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (HttpException $e) {
            $headers = $e->getHeaders();

            return $this->error($e->getMessage(), $e->getStatusCode(), [
                'lock_reason' => $headers['lock_reason'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Learning show failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Learning not found.', 404);
        }
    }

    /**
     * GET /api/v1/learnings/summary/{id}
     */
    public function summary(int $id): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $data = $this->learningService->getLearningSummary($id, $user);

            $resource = new LearningSummaryResource(
                $data['model'],
                $data['keyTakeaways'],
                $data['categoryTitle']
            );

            return $this->success('Learning summary fetched successfully.', $resource->toArray(request()));
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (HttpException $e) {
            $headers = $e->getHeaders();

            return $this->error($e->getMessage(), $e->getStatusCode(), [
                'lock_reason' => $headers['lock_reason'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return $this->error('Learning not found.', 404);
        }
    }

    /**
     * POST /api/v1/learnings/completed
     */
    public function complete(CompleteLearningRequest $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $data = $this->learningService->completeLearning(
                $request->validated()['learning_id'],
                $user,
                $request
            );

            return $this->success('Progress updated.', $data);
        } catch (\Throwable $e) {
            return $this->error('Progress not updated.', 400);
        }
    }

    /**
     * POST /api/v1/learnings/next
     */
    public function nextLearning(LearningNavigationRequest $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $data = $this->learningService->getNextLearning(
                $request->validated()['learning_id'],
                $user
            );

            return $this->success('Next learnings loaded.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Next learning not found.', 404);
        }
    }

    /**
     * POST /api/v1/learnings/previous
     */
    public function previousLearning(LearningNavigationRequest $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $data = $this->learningService->getPreviousLearning(
                $request->validated()['learning_id'],
                $user
            );

            return $this->success('Previous learnings loaded.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return $this->error('Previous learning not found.', 404);
        }
    }

    /**
     * POST /api/v1/learnings/checklist/update
     */
    public function updateChecklist(UpdateChecklistRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = auth('sanctum')->user();

            $data = $this->learningService->updateChecklist(
                (int) $validated['learning_id'],
                (int) $validated['element_id'],
                $validated['checked_items'],
                $user,
                $request
            );

            return $this->success('Checklist updated successfully.', $data);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        } catch (\Throwable $e) {
            return $this->error('Failed to update checklist.', 400);
        }
    }

    /* ------------------------------------------------------------------
     * Private category helpers
     * ----------------------------------------------------------------*/

    private function fetchByCategory(string $category): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $result = $this->learningService->getCategoryLearnings($category, $user, request());

            return $this->success('Learnings fetched successfully.', $result);
        } catch (\Throwable $e) {
            return $this->error('Learnings not found.', 404);
        }
    }

    private function fetchGroupedCategory(string $category, bool $compact = false, bool $stripGroupMeta = false): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            $result = $this->learningService->getGroupedCategoryLearnings($category, $user, request(), $compact, $stripGroupMeta);

            return $this->success('Learnings fetched successfully.', $result);
        } catch (\Throwable $e) {
            return $this->error('Learnings not found.', 404);
        }
    }
}
