<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $notifications = $query->paginate(20);

        // Get notification types for filter
        $types = Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->distinct()
            ->pluck('type');

        return view('notifications.index', compact('notifications', 'types'));
    }

    /**
     * Get unread notifications for the authenticated user.
     */
    public function unread()
    {
        $notifications = Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $count = Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAsRead()
    {
        Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markOneAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->delete();
            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notificación no encontrada'
        ], 404);
    }

    /**
     * Get user notification preferences.
     */
    public function preferences()
    {
        $user = Auth::user();
        
        // Get user preferences (could be from a separate table or user attributes)
        $preferences = [
            'email_enabled' => $user->email_notifications_enabled ?? true,
            'push_enabled' => $user->push_notifications_enabled ?? true,
            'notification_types' => [
                'test_daily' => $user->notify_test_daily ?? true,
                'inventory_low' => $user->notify_inventory_low ?? true,
                'payment_overdue' => $user->notify_payment_overdue ?? true,
                'sale_completed' => $user->notify_sale_completed ?? true,
                'system' => $user->notify_system ?? true,
            ]
        ];

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update user notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'notification_types' => 'array',
            'notification_types.*' => 'boolean'
        ]);

        // Update user preferences (this would need to be adjusted based on your database structure)
        $user->email_notifications_enabled = $validated['email_enabled'] ?? true;
        $user->push_notifications_enabled = $validated['push_enabled'] ?? true;
        
        if (isset($validated['notification_types'])) {
            foreach ($validated['notification_types'] as $type => $enabled) {
                $field = 'notify_' . $type;
                $user->$field = $enabled;
            }
        }

        $user->save();

        return redirect()->back()->with('success', 'Preferencias de notificación actualizadas');
    }
}