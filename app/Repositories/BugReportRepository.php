<?php

namespace App\Repositories;

use App\Models\BugReport;
use App\Repositories\Interfaces\BugReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BugReportRepository implements BugReportRepositoryInterface
{
    protected BugReport $model;

    public function __construct(BugReport $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?BugReport
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): BugReport
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): BugReport
    {
        return $this->model->create($data);
    }

    public function update(BugReport $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(BugReport $model): ?bool
    {
        return $model->delete();
    }
}
