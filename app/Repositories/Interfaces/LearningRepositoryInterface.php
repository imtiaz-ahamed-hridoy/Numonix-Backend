<?php

namespace App\Repositories\Interfaces;

use App\Models\Learning;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for learning/lesson data access.
 */
interface LearningRepositoryInterface
{
    public function findOrFail(int $id): Learning;

    public function findById(int $id): ?Learning;

    public function getAllPublished(?string $category = null): Collection;

    public function getPublishedByCategory(string $category): Collection;

    public function getPublishedByCategoryOrdered(string $category): Collection;

    public function getGroupsByCategory(string $category): Collection;

    public function getQuizForLearning(int $learningId): ?\App\Models\Quiz;

    public function findElementById(int $id): ?\App\Models\LearningPageElement;

    public function findElementWithPage(int $id): ?\App\Models\LearningPageElement;

    public function getNextInCategory(Learning $learning): ?Learning;

    public function getPreviousInCategory(Learning $learning): ?Learning;

    public function getNextTwoInCategory(Learning $learning): Collection;

    public function getPreviousTwoInCategory(Learning $learning): Collection;

    public function getPositionInCategory(Learning $learning): int;

    public function loadPagesWithElements(Learning $learning): Learning;
}
