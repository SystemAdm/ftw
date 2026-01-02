<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuildingInside;
use App\Models\BuildingLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class OpenController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Open');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $userId = Crypt::decryptString($request->code);
            $user = User::findOrFail($userId);
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'Invalid QR code.']);
        }

        $isInside = BuildingInside::where('user_id', $user->id)->exists();

        if ($isInside) {
            // User is leaving
            BuildingInside::where('user_id', $user->id)->delete();
            BuildingLog::create([
                'user_id' => $user->id,
                'action' => 'out',
            ]);
            $message = "{$user->name} has left the building.";
        } else {
            // User is entering
            BuildingInside::create([
                'user_id' => $user->id,
                'entered_at' => now(),
            ]);
            BuildingLog::create([
                'user_id' => $user->id,
                'action' => 'in',
            ]);
            $message = "{$user->name} has entered the building.";
        }

        return back()->with('status', $message);
    }
}
