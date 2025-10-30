<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains production-specific configuration settings for the
    | Invoice Management System. These settings are optimized for security,
    | performance, and reliability in a production environment.
    |
    */

    // Security Settings
    'security' => [
        'force_https' => env('FORCE_HTTPS', true),
        'session_secure_cookie' => env('SESSION_SECURE_COOKIE', true),
        'trusted_proxies' => env('TRUSTED_PROXIES', '*'),
        'x_frame_options' => 'DENY',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains; preload',
        'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;",
    ],

    // Performance Settings
    'performance' => [
        'cache_driver' => env('CACHE_DRIVER', 'redis'),
        'session_driver' => env('SESSION_DRIVER', 'redis'),
        'queue_driver' => env('QUEUE_CONNECTION', 'redis'),
        'optimize_autoloader' => true,
        'compress_output' => true,
        'minify_assets' => true,
    ],

    // Rate Limiting
    'rate_limiting' => [
        'api' => env('API_RATE_LIMIT', 60), // requests per minute
        'login' => env('LOGIN_RATE_LIMIT', 5), // login attempts per minute
        'password_reset' => 3, // requests per hour
        'email_verification' => 6, // requests per hour
    ],

    // Backup Settings
    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'disk' => env('BACKUP_DISK', 's3'),
        'schedule' => env('BACKUP_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
        'retention_days' => 30,
        'exclude_tables' => [
            'sessions',
            'cache',
            'failed_jobs',
            'jobs',
        ],
    ],

    // Monitoring Settings
    'monitoring' => [
        'sentry_dsn' => env('SENTRY_LARAVEL_DSN'),
        'new_relic_enabled' => env('NEW_RELIC_ENABLED', false),
        'new_relic_app_name' => env('NEW_RELIC_APP_NAME', 'invoice-system'),
        'new_relic_license_key' => env('NEW_RELIC_LICENSE_KEY'),
        'log_level' => env('LOG_LEVEL', 'error'),
        'health_check_enabled' => true,
    ],

    // File Upload Settings
    'uploads' => [
        'max_size' => env('MAX_UPLOAD_SIZE', 10240), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'csv', 'xlsx', 'doc', 'docx'],
        'storage_disk' => env('FILESYSTEM_CLOUD', 's3'),
    ],

    // PDF Settings
    'pdf' => [
        'storage_disk' => env('PDF_DISK', 's3'),
        'expiry_days' => env('PDF_EXPIRY_DAYS', 30),
        'cleanup_enabled' => true,
    ],

    // Email Settings
    'email' => [
        'queue_enabled' => true,
        'throttle_enabled' => true,
        'max_per_minute' => 60,
        'verification_expiry' => 60, // minutes
    ],

    // Maintenance Settings
    'maintenance' => [
        'auto_cleanup' => true,
        'cleanup_days' => 7,
        'log_retention_days' => 30,
        'temp_file_cleanup' => true,
    ],

    // Security Headers
    'security_headers' => [
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
    ],

    // Database Settings
    'database' => [
        'read_write_splitting' => false,
        'read_connections' => [
            'read' => [
                'host' => env('DB_READ_HOST', env('DB_HOST', '127.0.0.1')),
                'port' => env('DB_READ_PORT', env('DB_PORT', '3306')),
                'database' => env('DB_READ_DATABASE', env('DB_DATABASE')),
                'username' => env('DB_READ_USERNAME', env('DB_USERNAME')),
                'password' => env('DB_READ_PASSWORD', env('DB_PASSWORD')),
            ],
        ],
    ],

    // Session Settings
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'encrypt' => true,
        'path' => '/',
        'domain' => env('SESSION_DOMAIN'),
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Queue Settings
    'queue' => [
        'failed_job_driver' => env('FAILED_JOB_DRIVER', 'database'),
        'failed_job_retention_days' => 7,
        'retry_after' => 90, // seconds
        'max_tries' => 3,
    ],

    // Logging Settings
    'logging' => [
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['single', 'slack'],
            ],
            'single' => [
                'driver' => 'single',
                'path' => storage_path('logs/laravel.log'),
                'level' => env('LOG_LEVEL', 'error'),
                'replace_placeholders' => true,
            ],
            'slack' => [
                'driver' => 'slack',
                'url' => env('LOG_SLACK_WEBHOOK_URL'),
                'username' => 'Laravel Log',
                'emoji' => ':boom:',
                'level' => 'error',
            ],
        ],
    ],
];