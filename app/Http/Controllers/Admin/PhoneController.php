<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $phoneNumbers = PhoneNumber::with(['users' => function ($query) {
            $query->withPivot(['primary', 'verified_at', 'verified_by']);
        }])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/phone/Index', [
            'phoneNumbers' => $phoneNumbers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'e164' => 'required|string|unique:phone_numbers,e164',
            'raw' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'primary' => 'boolean',
        ]);

        $phoneNumber = PhoneNumber::create([
            'e164' => $validated['e164'],
            'raw' => $validated['raw'] ?? $validated['e164'],
        ]);

        if (! empty($validated['user_id'])) {
            $phoneNumber->users()->attach($validated['user_id'], [
                'primary' => $validated['primary'] ?? false,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Phone number created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhoneNumber $phone)
    {
        $validated = $request->validate([
            'e164' => 'required|string|unique:phone_numbers,e164,'.$phone->id,
            'raw' => 'nullable|string',
        ]);

        $phone->update($validated);

        return back()->with('success', 'Phone number updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhoneNumber $phone)
    {
        $phone->delete();

        return back()->with('success', 'Phone number deleted successfully.');
    }

    /**
     * Associate a user with a phone number.
     */
    public function associate(Request $request, PhoneNumber $phone)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'primary' => 'boolean',
        ]);

        if ($phone->users()->where('user_id', $validated['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'This user is already associated with this phone number.']);
        }

        $phone->users()->attach($validated['user_id'], [
            'primary' => $validated['primary'] ?? false,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'User associated successfully.');
    }

    /**
     * Disassociate a user from a phone number.
     */
    public function disassociate(PhoneNumber $phone, User $user)
    {
        $phone->users()->detach($user->id);

        return back()->with('success', 'User disassociated successfully.');
    }

    /**
     * Toggle primary status for a user's phone number.
     */
    public function togglePrimary(PhoneNumber $phone, User $user)
    {
        $pivot = $phone->users()->where('user_id', $user->id)->first()->pivot;

        $newStatus = ! $pivot->primary;

        if ($newStatus) {
            // Unset other primary numbers for this user
            $user->phoneNumbers()->updateExistingPivot($user->phoneNumbers()->pluck('phone_numbers.id'), ['primary' => false]);
        }

        $phone->users()->updateExistingPivot($user->id, ['primary' => $newStatus]);

        return back()->with('success', 'Primary status updated.');
    }

    /**
     * Search for users.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $term = $request->query('q');

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
