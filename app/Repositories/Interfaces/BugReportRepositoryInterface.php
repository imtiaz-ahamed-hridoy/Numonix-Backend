<?php

namespace App\Repositories\Interfaces;

use App\Models\BugReport;
use Illuminate\Database\Eloquent\Collection;

interface BugReportRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?BugReport;
    public function findOrFail(int $id): BugReport;
    public function create(array $data): BugReport;
    public function update(BugReport $model, array $data): bool;
    public function delete(BugReport $model): ?bool;
}
