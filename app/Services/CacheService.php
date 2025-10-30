<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Cache duration in seconds
     */
    const CACHE_DURATION = 3600; // 1 hour
    const SHORT_CACHE_DURATION = 300; // 5 minutes
    const LONG_CACHE_DURATION = 86400; // 24 hours

    /**
     * Get dashboard statistics with caching
     */
    public static function getDashboardStats($userId): array
    {
        $cacheKey = "dashboard_stats_{$userId}";

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId) {
            $reportService = new ReportService();
            return $reportService->generateDashboardStats($userId);
        });
    }

    /**
     * Get customer statistics with caching
     */
    public static function getCustomerStats($userId): array
    {
        $cacheKey = "customer_stats_{$userId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return [
                'total_customers' => DB::table('customers')->where('user_id', $userId)->count(),
                'active_customers' => DB::table('customers')->where('user_id', $userId)->where('is_active', true)->count(),
                'customers_with_invoices' => DB::table('customers')
                    ->where('user_id', $userId)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('invoices')
                            ->whereRaw('invoices.customer_id = customers.id');
                    })
                    ->count(),
            ];
        });
    }

    /**
     * Get item statistics with caching
     */
    public static function getItemStats($userId): array
    {
        $cacheKey = "item_stats_{$userId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return [
                'total_items' => DB::table('items')->where('user_id', $userId)->count(),
                'active_items' => DB::table('items')->where('user_id', $userId)->where('is_active', true)->count(),
                'low_stock_items' => DB::table('items')
                    ->where('user_id', $userId)
                    ->whereColumn('stock_quantity', '<=', 'reorder_level')
                    ->where('reorder_level', '>', 0)
                    ->count(),
            ];
        });
    }

    /**
     * Get quote statistics with caching
     */
    public static function getQuoteStats($userId): array
    {
        $cacheKey = "quote_stats_{$userId}";

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId) {
            return [
                'total_quotes' => DB::table('quotes')->where('user_id', $userId)->count(),
                'draft_quotes' => DB::table('quotes')->where('user_id', $userId)->where('status', 'Draft')->count(),
                'sent_quotes' => DB::table('quotes')->where('user_id', $userId)->where('status', 'Sent')->count(),
                'converted_quotes' => DB::table('quotes')->where('user_id', $userId)->where('status', 'Converted')->count(),
                'total_value' => DB::table('quotes')->where('user_id', $userId)->sum('total'),
            ];
        });
    }

    /**
     * Get invoice statistics with caching
     */
    public static function getInvoiceStats($userId): array
    {
        $cacheKey = "invoice_stats_{$userId}";

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId) {
            return [
                'total_invoices' => DB::table('invoices')->where('user_id', $userId)->count(),
                'paid_invoices' => DB::table('invoices')->where('user_id', $userId)->where('status', 'paid')->count(),
                'sent_invoices' => DB::table('invoices')->where('user_id', $userId)->where('status', 'sent')->count(),
                'overdue_invoices' => DB::table('invoices')->where('user_id', $userId)->where('status', 'overdue')->count(),
                'total_revenue' => DB::table('invoices')->where('user_id', $userId)->where('status', 'paid')->sum('total'),
                'outstanding_amount' => DB::table('invoices')
                    ->where('user_id', $userId)
                    ->whereIn('status', ['sent', 'overdue'])
                    ->sum('total'),
            ];
        });
    }

    /**
     * Cache search results for items
     */
    public static function getCachedItemSearch($userId, $searchTerm, $limit = 10)
    {
        $cacheKey = "item_search_{$userId}_" . md5($searchTerm . $limit);

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId, $searchTerm, $limit) {
            return DB::table('items')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('sku', 'like', "%{$searchTerm}%");
                })
                ->select('id', 'name', 'sku', 'unit_price', 'description')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Cache search results for customers
     */
    public static function getCachedCustomerSearch($userId, $searchTerm, $limit = 10)
    {
        $cacheKey = "customer_search_{$userId}_" . md5($searchTerm . $limit);

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId, $searchTerm, $limit) {
            return DB::table('customers')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('company', 'like', "%{$searchTerm}%")
                          ->orWhere('email', 'like', "%{$searchTerm}%");
                })
                ->select('id', 'name', 'email', 'company', 'phone')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Cache revenue report data
     */
    public static function getCachedRevenueReport($userId, $startDate, $endDate)
    {
        $cacheKey = "revenue_report_{$userId}_" . md5($startDate . $endDate);

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId, $startDate, $endDate) {
            $reportService = new ReportService();
            return $reportService->generateRevenueReport($userId, $startDate, $endDate);
        });
    }

    /**
     * Cache customer performance report
     */
    public static function getCachedCustomerReport($userId, $limit = 20)
    {
        $cacheKey = "customer_report_{$userId}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId, $limit) {
            $reportService = new ReportService();
            return $reportService->generateCustomerReport($userId, $limit);
        });
    }

    /**
     * Cache user settings
     */
    public static function getCachedUserSettings($userId)
    {
        $cacheKey = "user_settings_{$userId}";

        return Cache::remember($cacheKey, self::LONG_CACHE_DURATION, function () use ($userId) {
            $user = \App\Models\User::find($userId);
            return $user->settings;
        });
    }

    /**
     * Cache top customers for dashboard
     */
    public static function getCachedTopCustomers($userId, $limit = 5)
    {
        $cacheKey = "top_customers_{$userId}_{$limit}";

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId, $limit) {
            $reportService = new ReportService();
            $stats = $reportService->generateDashboardStats($userId);
            return $stats['top_customers'];
        });
    }

    /**
     * Cache recent activity for dashboard
     */
    public static function getCachedRecentActivity($userId)
    {
        $cacheKey = "recent_activity_{$userId}";

        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($userId) {
            $reportService = new ReportService();
            $stats = $reportService->generateDashboardStats($userId);
            return $stats['recent_activity'];
        });
    }

    /**
     * Clear all caches for a specific user
     */
    public static function clearUserCache($userId): void
    {
        $patterns = [
            "dashboard_stats_{$userId}",
            "customer_stats_{$userId}",
            "item_stats_{$userId}",
            "quote_stats_{$userId}",
            "invoice_stats_{$userId}",
            "item_search_{$userId}_",
            "customer_search_{$userId}_",
            "revenue_report_{$userId}_",
            "customer_report_{$userId}_",
            "user_settings_{$userId}",
            "top_customers_{$userId}_",
            "recent_activity_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // Only attempt Redis operations if Redis is configured
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    if ($redis) {
                        $keys = $redis->keys("*{$pattern}*");

                        if (!empty($keys)) {
                            $keys = array_map(function ($key) {
                                return str_starts_with($key, config('cache.prefix', 'laravel_cache') . ':')
                                    ? substr($key, strlen(config('cache.prefix', 'laravel_cache') . ':'))
                                    : $key;
                            }, $keys);

                            Cache::deleteMultiple($keys);
                        }
                    }
                } catch (\Exception $e) {
                    // Redis not available, skip cleanup
                }
            } else {
                // For array cache, use simple pattern matching
                Cache::flush(); // Clear all as fallback
            }
        }
    }

    /**
     * Clear cache when items are modified
     */
    public static function clearItemCache($userId): void
    {
        $patterns = [
            "item_stats_{$userId}",
            "item_search_{$userId}",
            "dashboard_stats_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // Only attempt Redis operations if Redis is configured
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    if ($redis) {
                        $keys = $redis->keys("*{$pattern}*");

                        if (!empty($keys)) {
                            $keys = array_map(function ($key) {
                                return str_starts_with($key, config('cache.prefix', 'laravel_cache') . ':')
                                    ? substr($key, strlen(config('cache.prefix', 'laravel_cache') . ':'))
                                    : $key;
                            }, $keys);

                            Cache::deleteMultiple($keys);
                        }
                    }
                } catch (\Exception $e) {
                    // Redis not available, skip cleanup
                }
            } else {
                // For array cache, use simple pattern matching
                Cache::flush(); // Clear all as fallback
            }
        }
    }

    /**
     * Clear cache when customers are modified
     */
    public static function clearCustomerCache($userId): void
    {
        $patterns = [
            "customer_stats_{$userId}",
            "customer_search_{$userId}",
            "customer_report_{$userId}",
            "top_customers_{$userId}",
            "dashboard_stats_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // Only attempt Redis operations if Redis is configured
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    if ($redis) {
                        $keys = $redis->keys("*{$pattern}*");

                        if (!empty($keys)) {
                            $keys = array_map(function ($key) {
                                return str_starts_with($key, config('cache.prefix', 'laravel_cache') . ':')
                                    ? substr($key, strlen(config('cache.prefix', 'laravel_cache') . ':'))
                                    : $key;
                            }, $keys);

                            Cache::deleteMultiple($keys);
                        }
                    }
                } catch (\Exception $e) {
                    // Redis not available, skip cleanup
                }
            } else {
                // For array cache, use simple pattern matching
                Cache::flush(); // Clear all as fallback
            }
        }
    }

    /**
     * Clear cache when quotes are modified
     */
    public static function clearQuoteCache($userId): void
    {
        $patterns = [
            "quote_stats_{$userId}",
            "revenue_report_{$userId}",
            "recent_activity_{$userId}",
            "dashboard_stats_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // Only attempt Redis operations if Redis is configured
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    if ($redis) {
                        $keys = $redis->keys("*{$pattern}*");

                        if (!empty($keys)) {
                            $keys = array_map(function ($key) {
                                return str_starts_with($key, config('cache.prefix', 'laravel_cache') . ':')
                                    ? substr($key, strlen(config('cache.prefix', 'laravel_cache') . ':'))
                                    : $key;
                            }, $keys);

                            Cache::deleteMultiple($keys);
                        }
                    }
                } catch (\Exception $e) {
                    // Redis not available, skip cleanup
                }
            } else {
                // For array cache, use simple pattern matching
                Cache::flush(); // Clear all as fallback
            }
        }
    }

    /**
     * Clear cache when invoices are modified
     */
    public static function clearInvoiceCache($userId): void
    {
        $patterns = [
            "invoice_stats_{$userId}",
            "revenue_report_{$userId}",
            "outstanding_report_{$userId}",
            "recent_activity_{$userId}",
            "dashboard_stats_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // Only attempt Redis operations if Redis is configured
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    if ($redis) {
                        $keys = $redis->keys("*{$pattern}*");

                        if (!empty($keys)) {
                            $keys = array_map(function ($key) {
                                return str_starts_with($key, config('cache.prefix', 'laravel_cache') . ':')
                                    ? substr($key, strlen(config('cache.prefix', 'laravel_cache') . ':'))
                                    : $key;
                            }, $keys);

                            Cache::deleteMultiple($keys);
                        }
                    }
                } catch (\Exception $e) {
                    // Redis not available, skip cleanup
                }
            } else {
                // For array cache, use simple pattern matching
                Cache::flush(); // Clear all as fallback
            }
        }
    }
}