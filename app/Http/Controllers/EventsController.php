<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\OpeningHours;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, OpeningHours $openingHours): Response
    {
        $query = Event::query()
            ->where('status', 'published')
            ->where('event_start', '>=', now())
            ->with(['location:id,name'])
            ->orderBy('event_start');

        if (auth()->check()) {
            $query->withExists(['reservations as is_signed_up' => function ($query) {
                $query->where('user_id', auth()->id());
            }]);
        }

        $events = $query->paginate(12);

        $week = (int) $request->query('week', 0);
        $days = $openingHours->getForWeek($week);

        return Inertia::render('events/Index', compact('events', 'days', 'week'));
    }

    public function show(Event $event): Response
    {
        if ($event->status !== 'published') {
            if (! auth()->check()) {
                abort(404);
            }

            $this->authorize('view', $event);
        }

        $event->load(['location']);
        $event->loadCount('reservations');

        $isSignedUp = auth()->check() ? $event->reservations()->where('user_id', auth()->id())->exists() : false;

        return Inertia::render('events/Show', compact('event', 'isSignedUp'));
    }

    public function signup(Event $event): RedirectResponse
    {
        $this->authorize('signup', $event);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Check if signup is needed
        if (! $event->signup_needed) {
            return back()->with('error', __('pages.events.signup.messages.signup_not_required'));
        }

        // 2. Check if already signed up
        if ($event->reservations()->where('user_id', $user->id)->exists()) {
            return back()->with('error', __('pages.events.signup.messages.already_signed_up'));
        }

        // 3. Check time limits
        $now = now();
        if ($event->signup_start && $now->lt($event->signup_start)) {
            return back()->with('error', __('pages.events.signup.messages.not_started'));
        }
        if ($event->signup_end && $now->gt($event->signup_end)) {
            return back()->with('error', __('pages.events.signup.messages.ended'));
        }

        // 4. Check seats
        if ($event->number_of_seats === 0) {
            return back()->with('error', __('pages.events.signup.messages.signup_not_required'));
        }

        if ($event->number_of_seats !== null && $event->number_of_seats !== -1) {
            $reservedCount = $event->reservations()->count();
            if ($reservedCount >= $event->number_of_seats) {
                return back()->with('error', __('pages.events.signup.messages.full'));
            }
        }

        // 5. Check age limits
        if ($event->age_min !== null || $event->age_max !== null) {
            if (! $user->birthday) {
                return back()->with('error', __('pages.events.signup.messages.age_restricted'));
            }

            $age = $user->birthday->age;
            if ($event->age_min !== null && $age < $event->age_min) {
                return back()->with('error', __('pages.events.signup.messages.too_young', ['age' => $event->age_min]));
            }
            if ($event->age_max !== null && $age > $event->age_max) {
                return back()->with('error', __('pages.events.signup.messages.too_old', ['age' => $event->age_max]));
            }
        }

        $event->reservations()->attach($user->id);

        return back()->with('success', __('pages.events.signup.messages.success'));
    }

    public function cancelSignup(Event $event): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (! $event->reservations()->where('user_id', $user->id)->exists()) {
            return back()->with('error', __('pages.events.signup.messages.not_signed_up'));
        }

        $event->reservations()->detach($user->id);

        return back()->with('success', __('pages.events.signup.messages.cancelled'));
    }
}
