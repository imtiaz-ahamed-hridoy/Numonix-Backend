<?php

namespace App\Repositories\Interfaces;

use App\Models\EarTrainingLevel;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository interface for ear training level data access.
 */
interface EarTrainingLevelRepositoryInterface
{
    public function findOrFail(int $id): EarTrainingLevel;

    public function getPublishedLevels(): Collection;

    public function getNextLevel(int $currentId): ?EarTrainingLevel;

    public function getPositionInDifficulty(EarTrainingLevel $level): int;
}
