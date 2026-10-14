<?php

namespace App\Services;

use App\Models\Review;
use App\Notifications\AppNotification;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service layer for user reviews/ratings.
 *
 * Handles CRUD operations for reviews and sends
 * notifications on successful creation.
 */
class ReviewService
{
    protected ReviewRepositoryInterface $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Retrieve all active (published) reviews.
     *
     * @return Collection<int, Review>
     */
    public function getActiveReviews()
    {
        return $this->reviewRepository->findByStatus('Active');
    }

    /**
     * Create a new review and notify the user.
     *
     * @return Review Loaded with user:id,name relation.
     */
    public function createReview(array $validated): Review
    {
        $review = $this->reviewRepository->create([
            'user_id' => $validated['user_id'],
            'ratings' => $validated['ratings'],
            'comment' => $validated['comment'] ?? null,
            'status' => 'Active',
        ]);

        $review->user->notify(new AppNotification(
            'Review Submitted!',
            "Thank you for your {$review->ratings}-star review!",
            ['review_id' => $review->id, 'ratings' => $review->ratings],
            'review_created'
        ));

        return $review->load('user:id,name');
    }

    /**
     * Update an existing review, verifying the requesting user owns it.
     *
     * @return Review Loaded with user:id,name relation.
     *
     * @throws NotFoundHttpException
     * @throws HttpException 403 on user mismatch
     */
    public function updateReview(int $id, array $validated): Review
    {
        $review = $this->reviewRepository->findById($id);

        if (! $review) {
            throw new NotFoundHttpException('Review not found');
        }

        if ((int) $review->user_id !== (int) $validated['user_id']) {
            throw new HttpException(403, 'User mismatch for this review');
        }

        $this->reviewRepository->update($review, [
            'ratings' => $validated['ratings'],
            'comment' => $validated['comment'] ?? $review->comment,
            'status' => $validated['status'] ?? $review->status,
        ]);

        return $review->load('user:id,name');
    }

    /**
     * Delete a review by ID.
     *
     * @return Review The model snapshot before deletion.
     *
     * @throws NotFoundHttpException
     */
    public function deleteReview(int $id): Review
    {
        $review = $this->reviewRepository->findById($id);

        if (! $review) {
            throw new NotFoundHttpException('Review not found');
        }

        $this->reviewRepository->delete($review);

        return $review;
    }
}
