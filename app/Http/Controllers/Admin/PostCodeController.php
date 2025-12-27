<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostCodeRequest;
use App\Http\Requests\Admin\UpdatePostCodeRequest;
use App\Models\PostalCode;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostCodeController extends Controller
{
    public function index(): mixed
    {
        $postcodes = PostalCode::query()
            ->withTrashed()
            ->orderBy('postal_code')
            ->paginate(15);

        return Inertia::render('admin/postcodes/index', compact('postcodes'));
    }

    public function create(): Response
    {
        return Inertia::render('admin/postcodes/create');
    }

    public function store(StorePostCodeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var PostalCode $postcode */
        $postcode = PostalCode::query()->create($data);

        return redirect()->route('admin.postcodes.show', $postcode)->with('success', __('pages.settings.postcodes.messages.created'));
    }

    public function show(PostalCode $postcode): Response
    {
        return Inertia::render('admin/postcodes/show', [
            'postcode' => $postcode,
        ]);
    }

    public function edit(PostalCode $postcode): Response
    {
        return Inertia::render('admin/postcodes/edit', [
            'postcode' => $postcode,
        ]);
    }

    public function update(UpdatePostCodeRequest $request, PostalCode $postcode): RedirectResponse
    {
        $postcode->update($request->validated());

        return redirect()->route('admin.postcodes.show', $postcode)->with('success', __('pages.settings.postcodes.messages.updated'));
    }

    public function destroy(PostalCode $postcode): RedirectResponse
    {
        $postcode->delete();

        return redirect()->route('admin.postcodes.index')->with('success', __('pages.settings.postcodes.messages.deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        $postcode = PostalCode::withTrashed()->findOrFail($id);
        $postcode->restore();

        return redirect()->route('admin.postcodes.index')->with('success', __('pages.settings.postcodes.messages.restored'));
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $postcode = PostalCode::withTrashed()->findOrFail($id);
        $postcode->forceDelete();

        return redirect()->route('admin.postcodes.index')->with('success', __('pages.settings.postcodes.messages.force_deleted'));
    }
}
