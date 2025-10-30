<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Propaganistas\LaravelPhone\Rules\Phone as PhoneRule;

class PhoneController extends Controller
{
    /**
     * Show the phone management page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $phones = $user->phoneNumbers()
            ->get()
            ->map(function (PhoneNumber $p) {
                return [
                    'id' => $p->id,
                    'e164' => $p->e164,
                    'raw' => $p->raw,
                    'primary' => (bool) $p->pivot->primary,
                    'verified_at' => $p->pivot->verified_at,
                ];
            });

        return Inertia::render('settings/Phone', [
            'phones' => $phones,
        ]);
    }

    /**
     * Add a phone number to the authenticated user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', (new PhoneRule())->country(['NO', 'FR', 'SE', 'DE', 'US', 'GB', 'ES', 'DK', 'FI', 'NL', 'BE', 'CH', 'AT', 'IT', 'PT', 'PL', 'IE', 'IS'])],
        ]);

        $raw = trim($validated['phone']);
        try {
            $e164 = phone($raw, 'NO')->formatE164();
        } catch (\Throwable $e) {
            return back()->withErrors(['phone' => __('Invalid phone number.')]);
        }

        $phoneModel = PhoneNumber::firstOrCreate(['e164' => $e164], ['raw' => $raw]);

        // Avoid duplication: if already attached to this user, show a friendly error
        if ($request->user()->phoneNumbers()->where('phone_numbers.id', $phoneModel->id)->exists()) {
            return back()->withErrors(['phone' => __('This phone number is already added.')]);
        }

        // Ensure only one primary; if this is first phone, set as primary
        $isFirst = $request->user()->phoneNumbers()->count() === 0;
        $request->user()->phoneNumbers()->syncWithoutDetaching([
            $phoneModel->id => [
                'primary' => $isFirst,
            ],
        ]);

        return back();
    }

    /**
     * Remove a phone number from the authenticated user.
     */
    public function destroy(Request $request, PhoneNumber $phone): RedirectResponse
    {
        $user = $request->user();

        // Only detach if attached
        if ($user->phoneNumbers()->where('phone_numbers.id', $phone->id)->exists()) {
            $isPrimary = (bool) $user->phoneNumbers()->where('phone_numbers.id', $phone->id)->first()->pivot->primary;

            // Prohibit removal of primary phone number
            if ($isPrimary) {
                return back()->withErrors(['phone' => __('You cannot remove your primary phone number. Please make another number primary first.')]);
            }

            $user->phoneNumbers()->detach($phone->id);
        }

        return back();
    }

    /**
     * Mark a specific phone as primary for the authenticated user.
     */
    public function makePrimary(Request $request, PhoneNumber $phone): RedirectResponse
    {
        $user = $request->user();

        // Ensure the phone belongs to the user
        if (! $user->phoneNumbers()->where('phone_numbers.id', $phone->id)->exists()) {
            abort(404);
        }

        // Unset previous primaries for this user
        \DB::table('phone_number_user')
            ->where('user_id', $user->id)
            ->update(['primary' => false]);

        // Set this one as primary
        $user->phoneNumbers()->updateExistingPivot($phone->id, ['primary' => true]);

        return back();
    }
}
