<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when user registration fails due to a business-logic error
 * (not a database failure, which is handled by QueryException).
 */
class RegistrationException extends RuntimeException
{
    //
}
