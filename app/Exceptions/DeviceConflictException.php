<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a device identity conflict is detected during guest
 * device registration within a database transaction.
 *
 * This is an internal exception caught immediately by the controller
 * and converted to a proper HTTP JSON response. It never bubbles up
 * to the global exception handler.
 */
class DeviceConflictException extends RuntimeException
{
    private string $field;

    /**
     * @param  string  $field  The conflicting field ('device_id' or 'token')
     * @param  string  $value  The conflicting value
     */
    public function __construct(string $field, string $value)
    {
        parent::__construct("Duplicate {$field}: {$value}");
        $this->field = $field;
    }

    /**
     * Get the field that caused the conflict.
     */
    public function getField(): string
    {
        return $this->field;
    }
}
