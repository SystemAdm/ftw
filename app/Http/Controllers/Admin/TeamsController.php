<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamRequest;
use App\Http\Requests\Admin\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): mixed
    {
        $teams = Team::query()->withTrashed()->paginate(10);

        return Inertia::render('admin/teams/index', compact('teams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $users = User::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/teams/create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $userIds = $data['users'] ?? [];
        unset($data['users']);

        $team = Team::query()->create($data);
        if (! empty($userIds)) {
            $team->users()->sync($userIds);
        }

        return redirect()->route('admin.teams.show', $team)->with('success', __('pages.settings.teams.messages.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team): Response
    {
        $team->load('users:id,name');

        return Inertia::render('admin/teams/show', compact('team'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team): Response
    {
        $users = User::query()->select('id', 'name')->orderBy('name')->get();
        $team->load('users:id');

        return Inertia::render('admin/teams/edit', compact('team', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $userIds = $data['users'] ?? null;
        unset($data['users']);

        $team->update($data);
        if ($userIds !== null) {
            $team->users()->sync($userIds);
        }

        return redirect()->route('admin.teams.show', $team)->with('success', __('pages.settings.teams.messages.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()->route('admin.teams.index')->with('success', __('pages.settings.teams.messages.deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        $team = Team::withTrashed()->findOrFail($id);
        $team->restore();

        return redirect()->route('admin.teams.index')->with('success', __('pages.settings.teams.messages.restored'));
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $team = Team::withTrashed()->findOrFail($id);
        $team->forceDelete();

        return redirect()->route('admin.teams.index')->with('success', __('pages.settings.teams.messages.force_deleted'));
    }
}
