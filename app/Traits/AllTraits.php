<?php

namespace App\Traits;

/**
 * Aggregate trait that bundles all commonly-used traits into a single inclusion point.
 */
trait AllTraits
{
    use FileManager, ChunkFileUpload, ImagePathTrait, ApiResponse, DatabaseExportable, HasFilter, TestPerpose, UnitConverter, TableAction;

}
