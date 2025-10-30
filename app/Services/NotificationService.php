<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Item;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public static function create($userId, string $type, string $title, string $message, array $data = null, string $actionUrl = null, string $actionText = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
        ]);
    }

    /**
     * Quote notifications
     */
    public static function quoteCreated(Quote $quote): Notification
    {
        return self::create(
            $quote->user_id,
            'quote_created',
            'New Quote Created',
            "Quote #{$quote->quote_number} has been created for {$quote->customer->name}.",
            [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'customer_name' => $quote->customer->name,
                'total' => $quote->total,
            ],
            route('quotes.show', $quote),
            'View Quote'
        );
    }

    public static function quoteSent(Quote $quote): Notification
    {
        return self::create(
            $quote->user_id,
            'quote_sent',
            'Quote Sent',
            "Quote #{$quote->quote_number} has been sent to {$quote->customer->name}.",
            [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'customer_name' => $quote->customer->name,
            ],
            route('quotes.show', $quote),
            'View Quote'
        );
    }

    public static function quoteConverted(Quote $quote, Invoice $invoice): Notification
    {
        return self::create(
            $quote->user_id,
            'quote_converted',
            'Quote Converted to Invoice',
            "Quote #{$quote->quote_number} has been converted to Invoice #{$invoice->invoice_number}.",
            [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
            route('invoices.show', $invoice),
            'View Invoice'
        );
    }

    /**
     * Invoice notifications
     */
    public static function invoiceCreated(Invoice $invoice): Notification
    {
        return self::create(
            $invoice->user_id,
            'invoice_created',
            'New Invoice Created',
            "Invoice #{$invoice->invoice_number} has been created for {$invoice->customer->name}.",
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'total' => $invoice->total,
                'due_date' => $invoice->due_date->format('Y-m-d'),
            ],
            route('invoices.show', $invoice),
            'View Invoice'
        );
    }

    public static function invoiceSent(Invoice $invoice): Notification
    {
        return self::create(
            $invoice->user_id,
            'invoice_sent',
            'Invoice Sent',
            "Invoice #{$invoice->invoice_number} has been sent to {$invoice->customer->name}.",
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
            ],
            route('invoices.show', $invoice),
            'View Invoice'
        );
    }

    public static function invoicePaid(Invoice $invoice): Notification
    {
        return self::create(
            $invoice->user_id,
            'invoice_paid',
            'Invoice Paid',
            "Invoice #{$invoice->invoice_number} has been marked as paid!",
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'total' => $invoice->total,
            ],
            route('invoices.show', $invoice),
            'View Invoice'
        );
    }

    public static function invoiceOverdue(Invoice $invoice): Notification
    {
        return self::create(
            $invoice->user_id,
            'invoice_overdue',
            'Invoice Overdue',
            "Invoice #{$invoice->invoice_number} for {$invoice->customer->name} is overdue by {$invoice->due_date->diffInDays(now())} days.",
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'amount' => $invoice->total,
                'days_overdue' => $invoice->due_date->diffInDays(now()),
            ],
            route('invoices.show', $invoice),
            'View Invoice'
        );
    }

    /**
     * Customer notifications
     */
    public static function customerCreated(Customer $customer): Notification
    {
        return self::create(
            $customer->user_id,
            'customer_created',
            'New Customer Added',
            "New customer {$customer->name} has been added to your system.",
            [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'email' => $customer->email,
            ],
            route('customers.show', $customer),
            'View Customer'
        );
    }

    /**
     * Item notifications
     */
    public static function itemCreated(Item $item): Notification
    {
        return self::create(
            $item->user_id,
            'item_created',
            'New Item Added',
            "New item {$item->name} has been added to your inventory.",
            [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'unit_price' => $item->unit_price,
            ],
            route('items.show', $item),
            'View Item'
        );
    }

    /**
     * System notifications
     */
    public static function systemNotification($userId, string $title, string $message, array $data = null): Notification
    {
        return self::create(
            $userId,
            'system',
            $title,
            $message,
            $data
        );
    }

    /**
     * Security notifications
     */
    public static function securityAlert($userId, string $title, string $message, array $data = null): Notification
    {
        return self::create(
            $userId,
            'security',
            $title,
            $message,
            $data,
            route('settings.edit'),
            'Review Settings'
        );
    }

    public static function loginFromNewDevice($userId, string $deviceInfo, string $ipAddress): Notification
    {
        return self::securityAlert(
            $userId,
            'New Device Login',
            "Your account was accessed from a new device: {$deviceInfo} (IP: {$ipAddress})",
            [
                'device_info' => $deviceInfo,
                'ip_address' => $ipAddress,
            ]
        );
    }

    public static function twoFactorEnabled($userId): Notification
    {
        return self::securityAlert(
            $userId,
            '2FA Enabled',
            'Two-factor authentication has been enabled for your account.',
            null,
            route('2fa.manage'),
            'Manage 2FA'
        );
    }

    public static function twoFactorDisabled($userId): Notification
    {
        return self::securityAlert(
            $userId,
            '2FA Disabled',
            'Two-factor authentication has been disabled for your account.',
            null,
            route('2fa.setup'),
            'Enable 2FA'
        );
    }

    /**
     * Get unread notifications for a user
     */
    public static function getUnreadNotifications($userId, int $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user
     */
    public static function getAllNotifications($userId, int $limit = 50)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notification count for a user
     */
    public static function getUnreadCount($userId): int
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Mark notifications as read
     */
    public static function markAsRead($notificationIds): int
    {
        if (!is_array($notificationIds)) {
            $notificationIds = [$notificationIds];
        }

        return Notification::whereIn('id', $notificationIds)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId): int
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete old notifications (older than specified days)
     */
    public static function cleanupOldNotifications(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return Notification::where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Create bulk notifications for multiple users
     */
    public static function createBulk(array $userIds, string $type, string $title, string $message, array $data = null): int
    {
        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return Notification::insert($notifications) ? count($notifications) : 0;
    }
}