<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationsController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Notifications', [
            'notifications' => $request->user()->notifications()->paginate(20),
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $request->user()->unreadNotifications()->where('id', $id)->get()->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->delete();

        return back();
    }
}
