<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    // Public listing of events (e.g., only active and upcoming by default)
    public function index(Request $request): Response
    {
        $now = now();
        $filters = [
            'q' => $request->string('q')->toString(),
            'include_past' => $request->boolean('include_past'),
        ];

        $query = Event::query()->where(function ($q) use ($now, $filters) {
            if ($filters['include_past']) {
                // no time filter when including past events
                return;
            }
            // Only upcoming or currently running events
            $q->where(function ($sub) use ($now) {
                $sub->whereNull('event_end')->where('event_start', '>=', $now)
                    ->orWhere(function ($sub2) use ($now) {
                        $sub2->whereNotNull('event_end')
                             ->where('event_end', '>=', $now);
                    });
            });
        })->where(function ($q) use ($filters) {
            // Only show public-facing events: active status or null treated as public
            $q->where('status', 'active')->orWhereNull('status');
        });

        if ($filters['q']) {
            $term = '%' . $filters['q'] . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('excerpt', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        // Sort upcoming first by event_start ascending
        $events = $query->orderBy('event_start', 'asc')
                        ->paginate(12)
                        ->withQueryString();

        return Inertia::render('Event/Index', [
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    // Public detail view by ID (avoid using slug in URLs)
    public function show(Event $event): Response
    {
        // Only allow viewing active or null-status events
        if (!($event->status === 'active' || $event->status === null)) {
            abort(404);
        }

        $user = request()->user();
        $userReserved = false;
        if ($user) {
            $userReserved = $event->reservations()->where('users.id', $user->id)->exists();
        }

        return Inertia::render('Event/Show', [
            'event' => $event,
            'user_reserved' => $userReserved,
        ]);
    }

    protected function registrationOpen(Event $event): bool
    {
        if (!$event->signup_needed) return false;
        $now = now();
        $startOk = $event->signup_start ? $event->signup_start <= $now : true;
        $endOk = $event->signup_end ? $event->signup_end >= $now : true;
        return $startOk && $endOk;
    }

    public function reserve(Event $event)
    {
        $user = request()->user();
        if (!$user) {
            abort(403);
        }
        // Ensure event is visible publicly
        if (!($event->status === 'active' || $event->status === null)) {
            abort(404);
        }
        // Ensure registration is open
        if (!$this->registrationOpen($event)) {
            return back()->with('error', 'Registration is not open.');
        }
        // Attach without detaching existing
        $event->reservations()->syncWithoutDetaching([$user->id]);
        return back()->with('success', 'Seat reserved.');
    }

    public function unreserve(Event $event)
    {
        $user = request()->user();
        if (!$user) {
            abort(403);
        }
        // Ensure event is visible publicly
        if (!($event->status === 'active' || $event->status === null)) {
            abort(404);
        }
        $event->reservations()->detach($user->id);
        // Also remove from attendees/inside if present to mirror admin behavior
        $event->attendees()->detach($user->id);
        $event->inside()->detach($user->id);
        return back()->with('success', 'Reservation cancelled.');
    }
}
