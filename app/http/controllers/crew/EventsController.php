<?php

namespace App\Http\Controllers\Crew;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->with(['location:id,name'])
            ->latest('event_start')
            ->paginate(15);

        return Inertia::render('crew/events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event with its attendees, inside users, logs, and reservations.
     */
    public function show(Event $event): Response
    {
        $event->load([
            'location',
            'attendees' => fn ($query) => $query->latest('event_attendee.created_at'),
            'inside' => fn ($query) => $query->latest('event_inside.created_at'),
            'reservations' => fn ($query) => $query->latest('event_reservation.created_at'),
            'logs' => fn ($query) => $query->with('user')->latest(),
        ]);

        return Inertia::render('crew/events/Show', [
            'event' => $event,
        ]);
    }
}
