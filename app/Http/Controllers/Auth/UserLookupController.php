<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;

class UserLookupController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query('q');

        if (empty($q)) {
            return response()->json(['users' => []]);
        }

        // If input contains only digits, spaces, dashes or plus, it might be a phone number
        if (preg_match('/^[0-9\+\-\s]+$/', $q)) {
            try {
                $phone = new PhoneNumber($q, array_merge(['AUTO'], config('custom.phone_fallback_countries', ['NO'])));
                if (! $phone->isValid()) {
                    throw ValidationException::withMessages([
                        'q' => [__('pages.auth.login.messages.phone_invalid')],
                    ]);
                }
            } catch (\Exception $e) {
                // If it's already a ValidationException, rethrow it
                if ($e instanceof ValidationException) {
                    throw $e;
                }

                // For other exceptions (e.g. parsing errors from the library),
                // if it only contains digits/phone chars and is not a valid email,
                // we should treat it as an invalid phone number.
                if (! str_contains($q, '@')) {
                    throw ValidationException::withMessages([
                        'q' => [__('pages.auth.login.messages.phone_invalid')],
                    ]);
                }
            }
        }

        $query = User::query();

        // Search by email or phone
        $matchType = 'email'; // default
        $formattedPhone = null;

        $query->where(function ($query) use ($q, &$matchType, &$formattedPhone) {
            if (str_contains($q, '@')) {
                $query->where('email', 'like', "%{$q}%");
                $matchType = 'email';

                return;
            }

            try {
                $phone = new PhoneNumber($q, array_merge(['AUTO'], config('custom.phone_fallback_countries', ['NO'])));

                if ($phone->isValid()) {
                    $e164 = $phone->formatE164();
                    $formattedPhone = $e164;
                    $query->whereHas('phoneNumbers', function ($query) use ($e164) {
                        $query->where('e164', 'like', "%{$e164}%")
                            ->orWhere('raw', 'like', "%{$e164}%");
                    });
                    $matchType = 'phone';

                    return;
                }
            } catch (\Exception $e) {
                // Already handled above
            }

            $query->where('username', 'like', "%{$q}%");
            $matchType = 'username';
        });

        $users = $query->limit(10)->get()->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'hasGoogle' => ! empty($user->google_id),
                'hasPassword' => ! empty($user->password),
            ];
        });

        return response()->json([
            'users' => $users,
            'matchType' => $matchType,
            'formattedPhone' => $formattedPhone,
        ]);
    }
}
