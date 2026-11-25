<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(
        Request $request,
        AdminNotificationService $notifications
    ) {
        return view('admin.notifications.index', [
            'notifications' => $notifications->paginate(
                $request->user()
            ),
            'unreadCount' => $notifications->unreadCount(
                $request->user()
            ),
        ]);
    }

    public function read(
        Request $request,
        int $auditLog,
        AdminNotificationService $notifications
    ) {
        $notifications->markRead(
            $request->user(),
            $auditLog
        );

        return redirect()->route(
            'admin.audit-log.show',
            ['auditLog' => $auditLog]
        );
    }

    public function readAll(
        Request $request,
        AdminNotificationService $notifications
    ) {
        $notifications->markAllRead(
            $request->user()
        );

        return redirect()
            ->route('admin.notifications.index')
            ->with(
                'success',
                'Semua notifikasi telah ditandai dibaca.'
            );
    }
}
