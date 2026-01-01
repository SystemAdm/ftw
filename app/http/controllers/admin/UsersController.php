<?php

namespace App\http\controllers\admin;

use App\http\controllers\Controller;
use App\models\User;
use App\models\UserBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::withTrashed()
            ->with(['bans', 'verifier:id,name'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/users/Index', compact('users'));
    }

    /**
     * Verify a user.
     */
    public function verify(User $user)
    {
        $user->update([
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'User verified successfully.');
    }

    /**
     * Send password reset link to user.
     */
    public function resetPassword(User $user)
    {
        Password::broker()->sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Password reset link sent.');
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'banned_to' => 'nullable|date|after:now',
        ]);

        $user->update([
            'banned_at' => now(),
            'banned_to' => $request->banned_to,
            'banned_by' => auth()->id(),
            'ban_reason' => $request->reason,
        ]);

        UserBan::create([
            'user_id' => $user->id,
            'banned_at' => now(),
            'banned_to' => $request->banned_to,
            'banned_by' => auth()->id(),
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'User banned successfully.');
    }

    /**
     * Unban a user.
     */
    public function unban(User $user)
    {
        $user->update([
            'banned_at' => null,
            'banned_to' => null,
            'banned_by' => null,
            'ban_reason' => null,
        ]);

        $user->bans()->where(function ($query) {
            $query->whereNull('banned_to')
                ->orWhere('banned_to', '>', now());
        })->delete();

        return back()->with('success', 'User unbanned successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::withTrashed()
            ->with([
                'bans.bannedBy',
                'verifier:id,name',
                'teams',
                'postalCode',
                'phoneNumbers',
                'guardians',
                'minors',
                'logs' => fn ($query) => $query->with('event')->latest()->limit(50),
                'buildingLogs' => fn ($query) => $query->latest()->limit(50),
            ])
            ->findOrFail($id);

        return Inertia::render('admin/users/Show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
