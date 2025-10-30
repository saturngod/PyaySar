<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an audit event
     */
    public static function log(string $action, string $description, $model = null, array $oldValues = null, array $newValues = null, Request $request = null): AuditLog
    {
        $user = Auth::user();
        $request = $request ?? request();

        $logData = [
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ];

        if ($model) {
            $logData['model_type'] = get_class($model);
            $logData['model_id'] = $model->id;
        }

        return AuditLog::create($logData);
    }

    /**
     * Log user login
     */
    public static function logLogin($user, $description = 'User logged in', Request $request = null): AuditLog
    {
        return self::log('login', $description, $user, null, null, $request);
    }

    /**
     * Log user logout
     */
    public static function logLogout($user, $description = 'User logged out', Request $request = null): AuditLog
    {
        $request = $request ?? request();

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => 'logout',
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'model_type' => $user ? get_class($user) : null,
            'model_id' => $user?->id,
        ]);
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin($email, $description = 'Failed login attempt', Request $request = null): AuditLog
    {
        $request = $request ?? request();

        return AuditLog::create([
            'user_id' => null,
            'action' => 'failed_login',
            'description' => $description . ' for email: ' . $email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'new_values' => ['email' => $email],
        ]);
    }

    /**
     * Log model creation
     */
    public static function logCreated($model, $description = null): AuditLog
    {
        $description = $description ?? class_basename($model) . " created";
        return self::log('created', $description, $model, null, $model->toArray());
    }

    /**
     * Log model update
     */
    public static function logUpdated($model, $oldValues = null, $newValues = null, $description = null): AuditLog
    {
        $description = $description ?? class_basename($model) . " updated";
        return self::log('updated', $description, $model, $oldValues, $newValues);
    }

    /**
     * Log model deletion
     */
    public static function logDeleted($model, $description = null): AuditLog
    {
        $description = $description ?? class_basename($model) . " deleted";
        return self::log('deleted', $description, $model, $model->toArray(), null);
    }

    /**
     * Log API token creation
     */
    public static function logTokenCreated($user, $tokenName, Request $request = null): AuditLog
    {
        return self::log('token_created', "API token '{$tokenName}' created", $user, null, ['token_name' => $tokenName], $request);
    }

    /**
     * Log API token revocation
     */
    public static function logTokenRevoked($user, $tokenName, Request $request = null): AuditLog
    {
        return self::log('token_revoked', "API token '{$tokenName}' revoked", $user, ['token_name' => $tokenName], null, $request);
    }

    /**
     * Log security-related events
     */
    public static function logSecurityEvent(string $event, string $description, $user = null, array $details = null, Request $request = null): AuditLog
    {
        return self::log($event, $description, $user, null, $details, $request);
    }

    /**
     * Get audit logs for a user
     */
    public static function getUserLogs($userId, int $limit = 50)
    {
        return AuditLog::where('user_id', $userId)
            ->with('user')
            ->orderBy('performed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a model
     */
    public static function getModelLogs($model, int $limit = 50)
    {
        return AuditLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->with('user')
            ->orderBy('performed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent security events
     */
    public static function getRecentSecurityEvents(int $limit = 100)
    {
        $securityActions = [
            'login', 'logout', 'failed_login', 'token_created', 'token_revoked',
            'password_change', '2fa_enabled', '2fa_disabled', 'security_breach'
        ];

        return AuditLog::whereIn('action', $securityActions)
            ->with('user')
            ->orderBy('performed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}