<?php

namespace App\Repositories;

use App\Models\EarTrainingLevel;
use App\Repositories\Interfaces\EarTrainingLevelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for ear training level data access.
 */
class EarTrainingLevelRepository implements EarTrainingLevelRepositoryInterface
{
    public function findOrFail(int $id): EarTrainingLevel
    {
        return EarTrainingLevel::findOrFail($id);
    }

    public function getPublishedLevels(): Collection
    {
        return EarTrainingLevel::where('is_active', true)
            ->orderBy('id', 'ASC')
            ->with(['stage' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get();
    }

    public function getNextLevel(int $currentId): ?EarTrainingLevel
    {
        return EarTrainingLevel::where('is_active', true)
            ->where('id', '>', $currentId)
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function getPositionInDifficulty(EarTrainingLevel $level): int
    {
        return EarTrainingLevel::where('is_active', true)
            ->where('ear_training_stage_id', $level->ear_training_stage_id)
            ->where('id', '<=', $level->id)
            ->count();
    }
}
