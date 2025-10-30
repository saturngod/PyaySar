<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    /**
     * Handle user login and return API token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log failed API login attempt
            AuditService::logFailedLogin($request->email, 'Failed API login attempt', $request);
            return $this->error('Invalid credentials', 401);
        }

        $deviceName = $request->device_name ?? $request->userAgent() ?? 'Unknown Device';
        $token = $user->createToken($deviceName);

        // Log successful API login and token creation
        AuditService::logLogin($user, 'User logged in via API', $request);
        AuditService::logTokenCreated($user, $deviceName, $request);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], 'Login successful');
    }

    /**
     * Handle user logout and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        $tokenName = $token->name ?? 'Unknown Device';

        $request->user()->currentAccessToken()->delete();

        // Log API logout and token revocation
        AuditService::logLogout($user, 'User logged out from API', $request);
        AuditService::logTokenRevoked($user, $tokenName, $request);

        return $this->success(null, 'Logout successful');
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success(null, 'Logged out from all devices');
    }

    /**
     * Get current user information
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 'User information retrieved successfully');
    }

    /**
     * Get all active tokens for the user
     */
    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'name', 'created_at', 'last_used_at', 'expires_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($tokens, 'Tokens retrieved successfully');
    }

    /**
     * Revoke a specific token
     */
    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (!$token) {
            return $this->error('Token not found', 404);
        }

        // Don't allow revoking the current token via this method
        if ($token->id === $request->user()->currentAccessToken()->id) {
            return $this->error('Cannot revoke current token via this endpoint', 422);
        }

        $token->delete();

        return $this->success(null, 'Token revoked successfully');
    }

    /**
     * Refresh the current token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Revoke current token
        $currentToken->delete();

        // Create new token
        $deviceName = $currentToken->name ?? 'API Token';
        $newToken = $user->createToken($deviceName);

        return $this->success([
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->expires_at,
        ], 'Token refreshed successfully');
    }
}