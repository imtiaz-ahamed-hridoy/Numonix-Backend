<?php

namespace App\Http\Controllers\API\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Review\StoreReviewRequest;
use App\Http\Requests\API\Review\UpdateReviewRequest;
use App\Services\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API controller for review CRUD operations.
 */
class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * Get all active reviews.
     */
public function index(): JsonResponse
    {
        $reviews = $this->reviewService->getActiveReviews();

        return $this->ok(
            $reviews->isNotEmpty() ? 'Reviews found' : 'No data found',
            $reviews
        );
    }

    /**
     * Store a newly created review.
     */
public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->reviewService->createReview($request->validated());

        return $this->success('Review created successfully', $review);
    }

    /**
     * Update an existing review.
     */
public function update(UpdateReviewRequest $request, $id): JsonResponse
    {
        try {
            $review = $this->reviewService->updateReview((int) $id, $request->validated());

            return $this->success('Review updated successfully', $review);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Delete a review by ID.
     */
public function destroy($id): JsonResponse
    {
        try {
            $review = $this->reviewService->deleteReview((int) $id);

            return $this->success('Review deleted successfully', $review);
        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }
}
