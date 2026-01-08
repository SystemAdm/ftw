<?php

namespace App\Http\Controllers\Crew;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crew\StoreEventRequest;
use App\Http\Requests\Crew\UpdateEventRequest;
use App\Models\Event;
use App\Models\Location;
use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the events.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Event::class);

        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        $events = Event::query()
            ->with(['location:id,name', 'team:id,name'])
            ->when(! $isGlobalAdmin, function ($query) use ($user) {
                $query->whereIn('team_id', $user->teams->pluck('id'));
            })
            ->latest('event_start')
            ->paginate(15);

        return Inertia::render('crew/events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Event::class);

        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        $teams = Team::query()
            ->when(! $isGlobalAdmin, function ($query) use ($user) {
                $query->whereIn('id', $user->teams->pluck('id'));
            })
            ->select('id', 'name')
            ->get();

        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('crew/events/Create', [
            'teams' => $teams,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);
        $data = $request->validated();

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $data['team_id'])) {
            abort(403);
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('events', 'public');
        } elseif ($request->filled('image_path')) {
            $data['image_path'] = $request->image_path;
        }

        $event = Event::query()->create($data);

        return redirect()->route('crew.events.show', $event)->with('success', __('pages.admin.events.messages.created'));
    }

    /**
     * Display the specified event with its attendees, inside users, logs, and reservations.
     */
    public function show(Request $request, Event $event): Response
    {
        $this->authorize('view', $event);

        $event->load([
            'location',
            'team',
            'attendees' => fn ($query) => $query->latest('event_attendee.created_at'),
            'inside' => fn ($query) => $query->latest('event_inside.created_at'),
            'reservations' => fn ($query) => $query->latest('event_reservation.created_at'),
            'logs' => fn ($query) => $query->with('user')->latest(),
        ]);

        return Inertia::render('crew/events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Event $event): Response
    {
        $this->authorize('update', $event);

        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        $teams = Team::query()
            ->when(! $isGlobalAdmin, function ($query) use ($user) {
                $query->whereIn('id', $user->teams->pluck('id'));
            })
            ->select('id', 'name')
            ->get();

        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('crew/events/Edit', [
            'event' => $event,
            'teams' => $teams,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);
        $data = $request->validated();

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $data['team_id'])) {
            abort(403);
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('events', 'public');
        } elseif ($request->filled('image_path')) {
            $data['image_path'] = $request->image_path;
        }

        $event->update($data);

        return redirect()->route('crew.events.show', $event)->with('success', __('pages.admin.events.messages.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('crew.events.index')->with('success', __('pages.admin.events.messages.deleted'));
    }
}
