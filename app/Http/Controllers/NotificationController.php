<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(NotificationService $notificationService): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        $notifications = $notificationService->forUser($user);

        return response()->json([
            'notifications' => $notifications->map(fn (AppNotification $notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'createdAt' => optional($notification->created_at)->toIso8601String(),
                'expiresAt' => optional($notification->expires_at)->toDateString(),
                'isRead' => $notification->is_read,
                'isHidden' => $notification->is_hidden,
                'url' => $notification->url,
            ])->values(),
            'unreadCount' => AppNotification::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->where('is_hidden', false)
                ->where('is_read', false)
                ->count(),
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        AppNotification::query()
            ->where('id', $id)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'unreadCount' => AppNotification::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->where('is_hidden', false)
                ->where('is_read', false)
                ->count(),
        ]);
    }

    public function hide(int $id): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        AppNotification::query()
            ->where('id', $id)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('is_hidden', false)
            ->update([
                'is_hidden' => true,
                'hidden_at' => now(),
            ]);

        return response()->json([
            'unreadCount' => AppNotification::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->where('is_hidden', false)
                ->where('is_read', false)
                ->count(),
        ]);
    }
}
