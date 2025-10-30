<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for 2FA
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for 2FA setup
     */
    public function generateQrCodeUrl(User $user, string $secretKey): string
    {
        $appName = config('app.name', 'Invoice System');
        return $this->google2fa->getQRCodeUrl(
            $appName,
            $user->email,
            $secretKey
        );
    }

    /**
     * Verify 2FA code
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for user
     */
    public function enableTwoFactor(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        if ($this->verifyCode($user, $code)) {
            $user->update([
                'two_factor_confirmed_at' => now(),
            ]);

            // Log 2FA enablement
            AuditService::logSecurityEvent(
                '2fa_enabled',
                'Two-factor authentication enabled',
                $user
            );

            return true;
        }

        return false;
    }

    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor(User $user, string $password): bool
    {
        if (!password_verify($password, $user->password)) {
            return false;
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_backup_code' => null,
        ]);

        // Log 2FA disablement
        AuditService::logSecurityEvent(
            '2fa_disabled',
            'Two-factor authentication disabled',
            $user
        );

        return true;
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(8) . '-' . Str::random(4));
        }
        return $codes;
    }

    /**
     * Generate backup code
     */
    public function generateBackupCode(): string
    {
        return strtoupper(Str::random(12));
    }

    /**
     * Verify recovery code
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode($user->two_factor_recovery_codes, true);

        if (in_array($code, $recoveryCodes)) {
            // Remove the used code
            $remainingCodes = array_filter($recoveryCodes, function ($c) use ($code) {
                return $c !== $code;
            });

            $user->update([
                'two_factor_recovery_codes' => array_values($remainingCodes),
            ]);

            // Log recovery code usage
            AuditService::logSecurityEvent(
                'recovery_code_used',
                'Two-factor recovery code used',
                $user,
                ['code_used' => substr($code, 0, 4) . '****']
            );

            return true;
        }

        return false;
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        if (!$user->two_factor_backup_code) {
            return false;
        }

        if (hash_equals($user->two_factor_backup_code, $code)) {
            // Generate new backup code after use
            $newBackupCode = $this->generateBackupCode();
            $user->update([
                'two_factor_backup_code' => $newBackupCode,
            ]);

            // Log backup code usage
            AuditService::logSecurityEvent(
                'backup_code_used',
                'Two-factor backup code used',
                $user
            );

            return true;
        }

        return false;
    }

    /**
     * Setup 2FA for user (generate and store secret)
     */
    public function setupTwoFactor(User $user): array
    {
        $secretKey = $this->generateSecretKey();
        $qrCodeUrl = $this->generateQrCodeUrl($user, $secretKey);
        $recoveryCodes = $this->generateRecoveryCodes();
        $backupCode = $this->generateBackupCode();

        $user->update([
            'two_factor_secret' => Crypt::encryptString($secretKey),
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_backup_code' => $backupCode,
        ]);

        return [
            'secret_key' => $secretKey,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $recoveryCodes,
            'backup_code' => $backupCode,
        ];
    }

    /**
     * Check if user has 2FA enabled
     */
    public function hasTwoFactorEnabled(User $user): bool
    {
        return !is_null($user->two_factor_confirmed_at);
    }

    /**
     * Get current recovery codes count
     */
    public function getRecoveryCodesCount(User $user): int
    {
        if (!$user->two_factor_recovery_codes) {
            return 0;
        }

        return count(json_decode($user->two_factor_recovery_codes, true));
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(User $user, string $password): bool
    {
        if (!password_verify($password, $user->password)) {
            return false;
        }

        $newCodes = $this->generateRecoveryCodes();
        $user->update([
            'two_factor_recovery_codes' => $newCodes,
        ]);

        // Log recovery codes regeneration
        AuditService::logSecurityEvent(
            'recovery_codes_regenerated',
            'Two-factor recovery codes regenerated',
            $user
        );

        return true;
    }

    /**
     * Generate QR code image (inline SVG)
     */
    public function generateQrCodeImage(string $url): string
    {
        try {
            $qrCode = new \PragmaRX\Google2FA\Support\QRCode\QRCodeGenerator();
            return $qrCode->generate($url);
        } catch (\Exception $e) {
            // Fallback to simple URL if QR generation fails
            return '';
        }
    }
}