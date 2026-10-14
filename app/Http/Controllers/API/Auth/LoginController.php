<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ChangePasswordRequest;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Services\Auth\AuthFacade;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * API controller for user login, logout, and session management.
 */
class LoginController extends Controller
{
    use ApiResponse;

    public AuthFacade $authFacade;

    public function __construct(AuthFacade $authFacade)
    {
        $this->authFacade = $authFacade;
    }

    /**
     * Authenticate a user and return an API token.
     */
    public function login(LoginRequest $request)
    {
        return $this->authFacade->login($request->email, $request->password);
    }

    /**
     * Logout the current user and revoke the current access token.
     */
    public function logout(Request $request)
    {
        return $this->authFacade->logout($request);
    }

    /**
     * Logout the user from all devices by revoking all tokens.
     */
    public function logoutAll(Request $request)
    {
        return $this->authFacade->logoutAll($request);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->authFacade->changePassword($request);
    }
}

