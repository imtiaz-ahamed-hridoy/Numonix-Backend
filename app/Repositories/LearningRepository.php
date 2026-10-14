<?php

namespace App\Repositories;

use App\Models\Learning;
use App\Repositories\Interfaces\LearningRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for learning/lesson data access.
 */
class LearningRepository implements LearningRepositoryInterface
{
    public function findOrFail(int $id): Learning
    {
        return Learning::findOrFail($id);
    }

    public function findById(int $id): ?Learning
    {
        return Learning::find($id);
    }

    public function getAllPublished(?string $category = null): Collection
    {
        $query = Learning::query()
            ->where('is_published', true)
            ->whereIn('status', ['active', 'coming_soon'])
            ->orderBy('id', 'ASC');

        if ($category !== null) {
            $query->where('category', $category);
        }

        return $query->get();
    }

    public function getPublishedByCategory(string $category): Collection
    {
        return Learning::query()
            ->where('is_published', true)
            ->where('category', $category)
            ->whereIn('status', ['active', 'coming_soon'])
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function getPublishedByCategoryOrdered(string $category): Collection
    {
        return $this->getPublishedByCategory($category);
    }

    public function getNextInCategory(Learning $learning): ?Learning
    {
        return Learning::query()
            ->where('is_published', true)
            ->where('category', $learning->category)
            ->whereIn('status', ['active', 'coming_soon'])
            ->where('id', '>', $learning->id)
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function getPreviousInCategory(Learning $learning): ?Learning
    {
        return Learning::query()
            ->where('is_published', true)
            ->where('category', $learning->category)
            ->whereIn('status', ['active', 'coming_soon'])
            ->where('id', '<', $learning->id)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function getNextTwoInCategory(Learning $learning): Collection
    {
        return Learning::query()
            ->where('is_published', true)
            ->where('category', $learning->category)
            ->whereIn('status', ['active', 'coming_soon'])
            ->where(function ($query) use ($learning) {
                $query->where('id', '>', $learning->id)
                    ->orWhere(function ($query) use ($learning) {
                        $query->where('id', $learning->id)
                            ->where('id', '>', $learning->id);
                    });
            })
            ->orderBy('id', 'ASC')
            ->limit(2)
            ->get();
    }

    public function getPreviousTwoInCategory(Learning $learning): Collection
    {
        return Learning::query()
            ->where('is_published', true)
            ->where('category', $learning->category)
            ->whereIn('status', ['active', 'coming_soon'])
            ->where(function ($query) use ($learning) {
                $query->where('id', '<', $learning->id)
                    ->orWhere(function ($query) use ($learning) {
                        $query->where('id', $learning->id)
                            ->where('id', '<', $learning->id);
                    });
            })
            ->orderBy('id', 'DESC')
            ->limit(2)
            ->get();
    }

    public function getPositionInCategory(Learning $learning): int
    {
        return Learning::where('is_published', true)
            ->where('category', $learning->category)
            ->whereIn('status', ['active', 'coming_soon'])
            ->where(function ($query) use ($learning) {
                $query->where('id', '<', $learning->id)
                    ->orWhere(function ($q) use ($learning) {
                        $q->where('id', $learning->id)
                            ->where('id', '<=', $learning->id);
                    });
            })
            ->count();
    }

    public function loadPagesWithElements(Learning $learning): Learning
    {
        if (!$learning->relationLoaded('pages')) {
            $learning->load('pages.elements');
        }
        return $learning;
    }

    public function getGroupsByCategory(string $category): Collection
    {
        return \App\Models\LearningGroup::where('category', $category)
            ->orderBy('id')
            ->get();
    }

    public function getQuizForLearning(int $learningId): ?\App\Models\Quiz
    {
        return \App\Models\Quiz::where('learning_id', $learningId)
            ->withCount('questions')
            ->first();
    }

    public function findElementById(int $id): ?\App\Models\LearningPageElement
    {
        return \App\Models\LearningPageElement::find($id);
    }

    public function findElementWithPage(int $id): ?\App\Models\LearningPageElement
    {
        return \App\Models\LearningPageElement::with('page')->find($id);
    }
}
