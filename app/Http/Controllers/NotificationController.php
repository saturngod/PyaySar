<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request): View
    {
        $query = auth()->user()->notifications();

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->get('type'));
        }

        // Filter by read status
        if ($request->has('status')) {
            if ($request->get('status') === 'unread') {
                $query->unread();
            } elseif ($request->get('status') === 'read') {
                $query->read();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = NotificationService::getUnreadCount(auth()->id());

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get notifications for API/JavaScript consumption
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);
        $type = $request->get('type');
        $status = $request->get('status');

        $query = auth()->user()->notifications();

        if ($type) {
            $query->ofType($type);
        }

        if ($status === 'unread') {
            $query->unread();
        } elseif ($status === 'read') {
            $query->read();
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'is_read' => $notification->isRead(),
                    'created_at' => $notification->created_at->toISOString(),
                    'action_url' => $notification->action_url,
                    'action_text' => $notification->action_text,
                    'data' => $notification->data,
                ];
            });

        $unreadCount = NotificationService::getUnreadCount(auth()->id());

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        if ($notification->markAsRead()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
                'unread_count' => NotificationService::getUnreadCount(auth()->id()),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to mark notification as read.'
        ], 500);
    }

    /**
     * Mark multiple notifications as read
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id',
        ]);

        // Verify ownership of all notifications
        $count = auth()->user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) marked as read.",
            'unread_count' => NotificationService::getUnreadCount(auth()->id()),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = NotificationService::markAllAsRead(auth()->id());

        return response()->json([
            'success' => true,
            'message' => "All {$count} notifications marked as read.",
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
            'unread_count' => NotificationService::getUnreadCount(auth()->id()),
        ]);
    }

    /**
     * Delete multiple notifications
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id',
        ]);

        $count = auth()->user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) deleted.",
            'unread_count' => NotificationService::getUnreadCount(auth()->id()),
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();

        $stats = [
            'total_notifications' => $user->notifications()->count(),
            'unread_notifications' => $user->notifications()->unread()->count(),
            'read_notifications' => $user->notifications()->read()->count(),
            'notifications_by_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'recent_notifications' => $user->notifications()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'type', 'title', 'created_at']),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get real-time notification count (for header badge)
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = NotificationService::getUnreadCount(auth()->id());

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }
}