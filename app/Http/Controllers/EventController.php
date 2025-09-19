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

    // Public detail view by slug
    public function show(string $slug): Response
    {
        $event = Event::where('slug', $slug)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->firstOrFail();

        return Inertia::render('Event/Show', [
            'event' => $event,
        ]);
    }
}
