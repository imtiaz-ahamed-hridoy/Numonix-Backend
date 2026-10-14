<?php

namespace App\Services;

// These models are not yet implemented in this codebase.
// Will be uncommented when Product/Offer modules are available.
// use App\Models\Offer;
// use App\Models\Product;

/**
 * Service layer for admin dashboard overview statistics.
 *
 * Provides aggregate counts for products, offers, and
 * other dashboard metrics.
 */
class DashboardOverviewService
{
    /**
     * Get dashboard overview stats.
     */
    public function getStats(): array
    {
        // Product and Offer modules are not yet implemented.
        // Return empty counts until those models exist.
        return [
            'Total Products' => 0,
            'Active Products' => 0,
            'Inactive Products' => 0,
            'Expired Offer' => 0,
        ];
    }
}
