<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    public function index(): Response
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

        return Inertia::render('events/index', compact('events'));
    }

    public function show(Event $event): Response
    {
        if ($event->status !== 'published' && ! auth()->user()?->hasRole('admin')) {
            abort(404);
        }

        $event->load(['location']);
        $event->loadCount('reservations');

        $isSignedUp = auth()->check() ? $event->reservations()->where('user_id', auth()->id())->exists() : false;

        return Inertia::render('events/show', compact('event', 'isSignedUp'));
    }

    public function signup(Event $event): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Check if signup is needed
        if (! $event->signup_needed) {
            return back()->with('error', 'Signup is not required for this event.');
        }

        // 2. Check if already signed up
        if ($event->reservations()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You are already signed up for this event.');
        }

        // 3. Check time limits
        $now = now();
        if ($event->signup_start && $now->lt($event->signup_start)) {
            return back()->with('error', 'Signup has not started yet.');
        }
        if ($event->signup_end && $now->gt($event->signup_end)) {
            return back()->with('error', 'Signup has ended.');
        }

        // 4. Check seats
        if ($event->number_of_seats === 0) {
            return back()->with('error', 'Signup is not required for this event.');
        }

        if ($event->number_of_seats !== null && $event->number_of_seats !== -1) {
            $reservedCount = $event->reservations()->count();
            if ($reservedCount >= $event->number_of_seats) {
                return back()->with('error', 'This event is full.');
            }
        }

        // 5. Check age limits
        if ($event->age_min !== null || $event->age_max !== null) {
            if (! $user->birthday) {
                return back()->with('error', 'Please set your birthdate in profile to signup for age-restricted events.');
            }

            $age = $user->birthday->age;
            if ($event->age_min !== null && $age < $event->age_min) {
                return back()->with('error', "You must be at least {$event->age_min} years old.");
            }
            if ($event->age_max !== null && $age > $event->age_max) {
                return back()->with('error', "This event is for users up to {$event->age_max} years old.");
            }
        }

        $event->reservations()->attach($user->id);

        return back()->with('success', 'You have successfully signed up for the event!');
    }

    public function cancelSignup(Event $event): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (! $event->reservations()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You are not signed up for this event.');
        }

        $event->reservations()->detach($user->id);

        return back()->with('success', 'You have successfully removed your signup for the event.');
    }
}
