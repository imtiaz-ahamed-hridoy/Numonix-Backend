<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when any step of the guest-device sync process fails.
 *
 * This is a non-fatal domain exception: the user has already been created
 * successfully. The caller (RegistrationService) should catch this, log it,
 * and continue - the user registration must NOT be aborted because of a
 * sync failure.
 */
class GuestDeviceSyncException extends RuntimeException
{
    //
}
