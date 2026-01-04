<?php

namespace App\Http\Controllers\Mod;

use App\Http\Controllers\Controller;
use App\Models\BuildingInside;
use App\Models\BuildingLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpenController extends Controller
{
    public function index(): Response
    {
        $inside = BuildingInside::with([
            'user' => fn ($q) => $q->select('id', 'name', 'email', 'avatar', 'created_at', 'birthday')
                ->with(['roles', 'teams:id,name', 'guardians:id,name', 'minors:id,name']),
        ])
            ->orderBy('entered_at', 'desc')
            ->get();

        $history = BuildingLog::with([
            'user' => fn ($q) => $q->select('id', 'name', 'email', 'avatar', 'created_at', 'birthday')
                ->with(['roles', 'teams:id,name', 'guardians:id,name', 'minors:id,name']),
        ])
            ->where('created_at', '>=', now()->subWeek())
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Format dates for frontend
        $inside->each(fn ($i) => $i->user->created_at_formatted = $i->user->created_at?->translatedFormat('F Y'));
        $history->getCollection()->each(fn ($h) => $h->user->created_at_formatted = $h->user->created_at?->translatedFormat('F Y'));

        return Inertia::render('mod/Open', [
            'inside' => $inside,
            'history' => $history,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        $isInside = BuildingInside::where('user_id', $user->id)->exists();

        if (! $isInside) {
            BuildingInside::create([
                'user_id' => $user->id,
                'entered_at' => now(),
            ]);

            BuildingLog::create([
                'user_id' => $user->id,
                'action' => 'in',
            ]);

            return back()->with('success', "{$user->name} has entered the building.");
        }

        return back()->with('error', "{$user->name} is already inside.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $wasInside = BuildingInside::where('user_id', $user->id)->exists();

        if ($wasInside) {
            BuildingInside::where('user_id', $user->id)->delete();

            BuildingLog::create([
                'user_id' => $user->id,
                'action' => 'out',
            ]);

            return back()->with('success', "{$user->name} has left the building.");
        }

        return back()->with('error', "{$user->name} was not inside.");
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $term = $request->query('q');

        if (empty($term)) {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->select('id', 'name', 'email')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get();

        return response()->json(['data' => $users]);
    }
}
