<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TwoFactorAuthController extends Controller
{
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->middleware('auth');
    }

    /**
     * Show 2FA setup form
     */
    public function showSetupForm(): View
    {
        $user = auth()->user();

        if ($this->twoFactorService->hasTwoFactorEnabled($user)) {
            return redirect()->route('2fa.manage')
                ->with('info', 'Two-factor authentication is already enabled.');
        }

        return view('auth.2fa-setup');
    }

    /**
     * Setup 2FA for user
     */
    public function setup(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($this->twoFactorService->hasTwoFactorEnabled($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is already enabled.'
            ], 422);
        }

        try {
            $setupData = $this->twoFactorService->setupTwoFactor($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'secret_key' => $setupData['secret_key'],
                    'qr_code_url' => $setupData['qr_code_url'],
                    'recovery_codes' => $setupData['recovery_codes'],
                    'backup_code' => $setupData['backup_code'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to setup two-factor authentication.'
            ], 500);
        }
    }

    /**
     * Confirm and enable 2FA
     */
    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|digits:6',
        ]);

        $user = auth()->user();

        if ($this->twoFactorService->hasTwoFactorEnabled($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is already enabled.'
            ], 422);
        }

        if ($this->twoFactorService->enableTwoFactor($user, $validated['code'])) {
            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been enabled successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code. Please try again.'
        ], 422);
    }

    /**
     * Show 2FA management page
     */
    public function showManageForm(): View
    {
        $user = auth()->user();

        if (!$this->twoFactorService->hasTwoFactorEnabled($user)) {
            return redirect()->route('2fa.setup')
                ->with('info', 'Please setup two-factor authentication first.');
        }

        $recoveryCodesCount = $this->twoFactorService->getRecoveryCodesCount($user);

        return view('auth.2fa-manage', compact('recoveryCodesCount'));
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'code' => 'nullable|string|digits:6',
        ]);

        $user = auth()->user();

        if (!$this->twoFactorService->hasTwoFactorEnabled($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.'
            ], 422);
        }

        // If code is provided, verify it first
        if (!empty($validated['code'])) {
            if (!$this->twoFactorService->verifyCode($user, $validated['code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code.'
                ], 422);
            }
        }

        if ($this->twoFactorService->disableTwoFactor($user, $validated['password'])) {
            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been disabled successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid password. Please try again.'
        ], 422);
    }

    /**
     * Show 2FA verification form
     */
    public function showVerificationForm(): View
    {
        return view('auth.2fa-verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();

        if (!$this->twoFactorService->hasTwoFactorEnabled($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.'
            ], 422);
        }

        // Try 2FA code first
        if (strlen($validated['code']) === 6 && is_numeric($validated['code'])) {
            if ($this->twoFactorService->verifyCode($user, $validated['code'])) {
                // Mark 2FA as verified in session
                session(['2fa_verified' => true]);

                return response()->json([
                    'success' => true,
                    'message' => 'Two-factor authentication verified successfully.'
                ]);
            }
        }

        // Try recovery code
        if ($this->twoFactorService->verifyRecoveryCode($user, $validated['code'])) {
            session(['2fa_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Recovery code verified successfully.',
                'warning' => 'You have used a recovery code. Consider regenerating your recovery codes.'
            ]);
        }

        // Try backup code
        if ($this->twoFactorService->verifyBackupCode($user, $validated['code'])) {
            session(['2fa_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Backup code verified successfully.',
                'warning' => 'You have used your backup code. A new backup code has been generated.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid code. Please try again.'
        ], 422);
    }

    /**
     * Get current 2FA status
     */
    public function status(): JsonResponse
    {
        $user = auth()->user();
        $isEnabled = $this->twoFactorService->hasTwoFactorEnabled($user);
        $recoveryCodesCount = $this->twoFactorService->getRecoveryCodesCount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $isEnabled,
                'recovery_codes_count' => $recoveryCodesCount,
                'confirmed_at' => $user->two_factor_confirmed_at,
            ]
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!$this->twoFactorService->hasTwoFactorEnabled($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.'
            ], 422);
        }

        if ($this->twoFactorService->regenerateRecoveryCodes($user, $validated['password'])) {
            $newCodes = json_decode($user->fresh()->two_factor_recovery_codes, true);

            return response()->json([
                'success' => true,
                'message' => 'Recovery codes have been regenerated successfully.',
                'data' => [
                    'recovery_codes' => $newCodes,
                    'count' => count($newCodes),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid password. Please try again.'
        ], 422);
    }

    /**
     * Show backup code
     */
    public function showBackupCode(): JsonResponse
    {
        $user = auth()->user();

        if (!$this->twoFactorService->hasTwoFactorEnabled($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.'
            ], 422);
        }

        // Log backup code view
        AuditService::logSecurityEvent(
            'backup_code_viewed',
            'Two-factor backup code viewed',
            $user
        );

        return response()->json([
            'success' => true,
            'data' => [
                'backup_code' => $user->two_factor_backup_code,
            ]
        ]);
    }
}