<?php

namespace App\Repositories;

use App\Models\FeatureSuggestion;
use App\Repositories\Interfaces\FeatureSuggestionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FeatureSuggestionRepository implements FeatureSuggestionRepositoryInterface
{
    protected FeatureSuggestion $model;

    public function __construct(FeatureSuggestion $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?FeatureSuggestion
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): FeatureSuggestion
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): FeatureSuggestion
    {
        return $this->model->create($data);
    }

    public function update(FeatureSuggestion $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(FeatureSuggestion $model): ?bool
    {
        return $model->delete();
    }
}
