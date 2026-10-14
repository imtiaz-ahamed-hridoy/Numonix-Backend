<?php

namespace App\Repositories\Interfaces;

interface FounderRepositoryInterface
{
    /**
     * Get statistics about the founder programme for the admin dashboard.
     *
     * @return array
     */
    public function getFounderStats(): array;
}
