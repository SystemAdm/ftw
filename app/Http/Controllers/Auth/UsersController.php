<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Weekday;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UsersController extends Controller
{
    public function loginForm(): Response
    {
        return Inertia::render('auth/LoginForm');
    }

    public function dashboard(Request $request): Response
    {
        // Build a 7-day window starting at "today + (7 * week)" where week is an offset from query.
        $week = (int) $request->query('week', 0);
        if ($week < 0) {
            $week = 0;
        }
        $start = Carbon::today()->copy()->addDays($week * 7);

        // Preload weekdays with exclusions to avoid N+1 queries
        $weekdays = Weekday::query()
            ->with(['exclusions', 'team:id,name,slug'])
            ->get()
            ->groupBy('weekday'); // 0 = Sunday ... 6 = Saturday
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $weekdayNumber = $date->dayOfWeek; // 0..6

            /** @var \Illuminate\Support\Collection<int, Weekday> $candidates */
            $candidates = $weekdays->get($weekdayNumber, collect());

            // Filter candidates that are active, not ended, and within optional date range
            $eligible = $candidates->filter(function (Weekday $w) use ($date): bool {
                if ($w->active !== true || $w->is_ended === true) {
                    return false;
                }
                if ($w->event_start !== null && $date->lt(Carbon::parse($w->event_start)->startOfDay())) {
                    return false;
                }
                if ($w->event_end !== null && $date->gt(Carbon::parse($w->event_end)->endOfDay())) {
                    return false;
                }

                return true;
            });

            // Separate those not excluded for this exact calendar date
            $eligibleNotExcluded = $eligible->filter(function (Weekday $w) use ($date): bool {
                $isExcluded = $w->exclusions->contains(function ($exclusion) use ($date): bool {
                    $excluded = $exclusion->excluded_date instanceof Carbon
                        ? $exclusion->excluded_date
                        : Carbon::parse($exclusion->excluded_date);

                    return $excluded->isSameDay($date);
                });

                return $isExcluded === false;
            });

            $hasWeekday = $eligibleNotExcluded->isNotEmpty();
            $isExcluded = $hasWeekday === false && $eligible->isNotEmpty();

            // Prefer name/description from an eligible item (even if excluded for the date),
            // falling back to first candidate as a last resort.
            $preferred = $eligible->first() ?? $candidates->first();

            // Prepare team (id, name, slug) if available on preferred weekday
            $team = null;
            if ($preferred !== null && $preferred->team !== null) {
                $team = [
                    'id' => $preferred->team->id,
                    'name' => $preferred->team->name,
                    'slug' => $preferred->team->slug,
                ];
            }

            $days[] = [
                'date' => $date->toDateString(),
                'weekday' => $weekdayNumber,
                'label' => $date->translatedFormat('d M'),
                'has_weekday' => $hasWeekday,
                'is_excluded' => $isExcluded,
                'weekday_label' => $date->translatedFormat('l'), // Full day name, locale aware
                // Include optional name/description for the first matching Weekday context
                'name' => optional($preferred)->name,
                'description' => optional($preferred)->description,
                'start_time' => optional($preferred)->start_time,
                'end_time' => optional($preferred)->end_time,
                'team' => $team,
            ];
        }

        $encryptedId = \Illuminate\Support\Facades\Crypt::encryptString((string) $request->user()->id);
        $qrCode = QrCode::size(200)->generate($encryptedId)->toHtml();

        return Inertia::render('Dashboard', [
            'days' => $days,
            'week' => $week,
            'qr_code' => $qrCode,
            'qr_code_value' => app()->isLocal() ? $encryptedId : null,
        ]);
    }

    public function welcome(): Response
    {
        return Inertia::render('Welcome');
    }

    public function confirmPasswordForm(): Response
    {
        return Inertia::render('auth/ConfirmPassword');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
