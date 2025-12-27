<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    public function index(): Response
    {
        $events = Event::query()
            ->with(['location:id,name'])
            ->latest('event_start')
            ->withTrashed()
            ->paginate(15);

        return Inertia::render('admin/events/index', compact('events'));
    }

    public function images(): JsonResponse
    {
        $files = Storage::disk('public')->files('events');
        $images = array_map(function ($file) {
            return [
                'path' => $file,
                'url' => Storage::disk('public')->url($file),
            ];
        }, $files);

        return response()->json($images);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('image')->store('events', 'public');

        return response()->json([
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    public function create(): Response
    {
        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/events/create', compact('locations'));
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('events', 'public');
        } elseif ($request->filled('image_path')) {
            $data['image_path'] = $request->image_path;
        }

        $event = Event::query()->create($data);

        return redirect()->route('admin.events.show', $event)->with('success', __('pages.admin.events.messages.created'));
    }

    public function show(Event $event): Response
    {
        $event->load(['location:id,name']);

        return Inertia::render('admin/events/show', compact('event'));
    }

    public function edit(Event $event): Response
    {
        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/events/edit', compact('event', 'locations'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('events', 'public');
        } elseif ($request->filled('image_path')) {
            $data['image_path'] = $request->image_path;
        }

        $event->update($data);

        return redirect()->route('admin.events.show', $event)->with('success', __('pages.admin.events.messages.updated'));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', __('pages.admin.events.messages.deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        Event::withTrashed()->findOrFail($id)->restore();

        return back()->with('success', __('pages.admin.events.messages.restored'));
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        Event::withTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.events.index')->with('success', __('pages.admin.events.messages.force_deleted'));
    }
}
