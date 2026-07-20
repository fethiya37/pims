<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function fetch()
    {
        $user = Auth::user();
        $unread = $user->notifications()
            ->wherePivot('read_at', null)
            ->orderBy('created_at', 'desc')
            ->get();

        $count = $unread->count();

        return response()->json([
            'count' => $count,
            'notifications' => $unread->map(function ($n) {
                if ($n->type === 'low_stock') {
                    $link = route('reports.low-stock');
                } else {
                    $link = route('reports.expiry');
                }

                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'message' => $n->message,
                    'reference' => $n->reference,
                    'link' => $link,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function markRead($id)
    {
        $user = Auth::user();
        $notification = Notification::findOrFail($id);
        $user->notifications()->updateExistingPivot($id, ['read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $user->notifications()->wherePivot('read_at', null)
            ->each(function ($n) use ($user) {
                $user->notifications()->updateExistingPivot($n->id, ['read_at' => now()]);
            });
        return back()->with('success', 'All notifications marked as read.');
    }
}