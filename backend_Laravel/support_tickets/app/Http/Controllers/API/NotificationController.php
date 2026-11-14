<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Notifications de l'utilisateur connecté.
 * Toutes les requêtes passent par $request->user()->notifications() :
 * un utilisateur ne peut jamais lire/modifier les notifications d'un autre.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->unreadNotifications()->latest()->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function all(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
