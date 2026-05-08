<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get()
            ->map(function ($notification, $index) {
                return [
                    'id' => $index + 1,
                    'notification_id' => $notification->id,
                    'type' => $notification->data['type'] ?? null,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'body' => $notification->data['body'] ?? '',
                    'order_id' => $notification->data['order_id'] ?? null,
                    'is_read' => $notification->read_at !== null,
                    'created_at' => optional($notification->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    public function destroy(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully.',
        ]);
    }
}