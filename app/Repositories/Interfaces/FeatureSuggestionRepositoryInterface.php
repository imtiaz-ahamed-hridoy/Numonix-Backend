<?php

namespace App\Repositories\Interfaces;

use App\Models\FeatureSuggestion;
use Illuminate\Database\Eloquent\Collection;

interface FeatureSuggestionRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?FeatureSuggestion;
    public function findOrFail(int $id): FeatureSuggestion;
    public function create(array $data): FeatureSuggestion;
    public function update(FeatureSuggestion $model, array $data): bool;
    public function delete(FeatureSuggestion $model): ?bool;
}
