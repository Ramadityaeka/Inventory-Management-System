<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        // If coming from a link (GET request), redirect to notifications page
        if ($request->isMethod('get')) {
            return redirect()->route('notifications.index');
        }

        // If AJAX or POST, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        }

        return redirect()->back()->with('success', 'Notifikasi telah dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function count()
    {
        $count = auth()->user()->notifications()
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Delete single notification
     */
    public function destroy(Request $request, $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->delete();

        // If AJAX request, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request)
    {
        $count = auth()->user()->notifications()
            ->where('is_read', true)
            ->delete();

        return redirect()->back()->with('success', "Berhasil menghapus {$count} notifikasi yang sudah dibaca.");
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(Request $request)
    {
        $count = auth()->user()->notifications()->delete();

        return redirect()->back()->with('success', "Berhasil menghapus {$count} notifikasi.");
    }
}