<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(), // draft | active | '' (all)
            'signup_needed' => $request->has('signup_needed') ? $request->boolean('signup_needed') : null,
            'trashed' => $request->string('trashed')->toString(), // all | only | without
        ];
        $sort = [
            'by' => $request->string('sort_by')->toString() ?: 'event_start',
            'dir' => strtolower($request->string('sort_dir')->toString() ?: 'desc'), // asc | desc
        ];

        $query = Event::query();

        // trashed filter
        switch ($filters['trashed'] === 'without' ? '' : $filters['trashed']) {
            case 'only':
                $query->onlyTrashed();
                break;
            case 'all':
                $query->withTrashed();
                break;
            default:
                // without trashed by default
                break;
        }

        // search title/slug/location_id
        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', $q)
                    ->orWhere('slug', 'like', $q)
                    ->orWhere('excerpt', 'like', $q);
            });
        }

        // status filter
        if (in_array($filters['status'], ['draft', 'active'], true)) {
            $query->where('status', $filters['status']);
        }

        // signup_needed filter
        if ($filters['signup_needed'] !== null) {
            $query->where('signup_needed', $filters['signup_needed']);
        }

        // whitelist sortable columns
        $sortable = ['title', 'event_start', 'event_end', 'signup_start', 'signup_end', 'created_at'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'event_start';
        $dir = $sort['dir'] === 'asc' ? 'asc' : 'desc';

        // Special case for title to sort case-insensitive
        if ($by === 'title') {
            $query->orderByRaw('LOWER(title) ' . ($dir === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $query->orderBy($by, $dir);
        }

        $events = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Event/Index', [
            'events' => $events,
            'filters' => $filters,
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    public function create(): Response
    {
        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Admin/Event/Edit', [
            'event' => null,
            'locations' => $locations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $event = Event::create($data);
        return redirect()->route('admin.events.index')->with('success', 'Event created');
    }

    public function edit(Event $event): Response
    {
        // Cast datetimes to ISO strings for Inertia (only when present)
        foreach (['event_start', 'event_end', 'signup_start', 'signup_end'] as $dt) {
            if ($event->{$dt}) {
                $event->{$dt} = $event->{$dt}->toIso8601String();
            }
        }
        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Admin/Event/Edit', [
            'event' => $event,
            'locations' => $locations,
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validateData($request, $event->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $event->update($data);
        return redirect()->route('admin.events.index')->with('success', 'Event updated');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();
        return back()->with('success', 'Event deleted');
    }

    public function restore(string $id): RedirectResponse
    {
        $event = Event::withTrashed()->findOrFail($id);
        if ($event->trashed()) {
            $event->restore();
        }
        return redirect()->route('admin.events.index')
            ->with('success', 'Event restored.');
    }

    public function forceDestroy(string $id): RedirectResponse
    {
        $event = Event::withTrashed()->findOrFail($id);
        $event->forceDelete();
        return redirect()->route('admin.events.index')
            ->with('success', 'Event permanently deleted.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($id)],
            'excerpt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'event_start' => ['required', 'date'],
            'event_end' => ['nullable', 'date', 'after_or_equal:event_start'],
            'signup_needed' => ['sometimes', 'boolean'],
            'signup_start' => ['nullable', 'date'],
            'signup_end' => ['nullable', 'date', 'after_or_equal:signup_start'],
            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'number_of_seats' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'status' => ['nullable', Rule::in(['draft', 'active'])],
        ]);
    }
}
