<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\StaffNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StaffNotificationController extends Controller
{
    public function index(StaffNotificationService $service): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        $notifications = $service->forUser($user);

        return response()->json([
            'notifications' => $notifications->map(fn (AppNotification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'createdAt' => optional($n->created_at)->toIso8601String(),
                'formattedDate' => optional($n->created_at)->format('d-m-Y'),
                'expiresAt' => optional($n->expires_at)->toDateString(),
                'isRead' => (bool) $n->is_read,
                'url' => $n->url,
            ])->values(),
            'unreadCount' => $service->unreadCount($user),
        ]);
    }

    public function markRead(int $id, StaffNotificationService $service): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        $unreadCount = $service->markRead($user, $id);

        return response()->json([
            'success' => true,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function dismiss(int $id, StaffNotificationService $service): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        $unreadCount = $service->dismiss($user, $id);

        return response()->json([
            'success' => true,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAllRead(StaffNotificationService $service): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('Admin'), 403);

        $unreadCount = $service->markAllRead($user);

        return response()->json([
            'success' => true,
            'unreadCount' => $unreadCount,
        ]);
    }
}
