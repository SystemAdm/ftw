<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'published_at' => $request->has('published_at') ? ($request->string('published_at')->toString() === '1' ? true : ($request->string('published_at')->toString() === '0' ? false : null)) : null,
            'trashed' => $request->string('trashed')->toString(), // all | only | without
            'tag' => $request->string('tag')->toString(),
        ];
        //dd($filters);
        $sort = [
            'by' => $request->string('sort_by')->toString() ?: 'created_at',
            'dir' => strtolower($request->string('sort_dir')->toString() ?: 'desc'), // asc | desc
        ];
        //dd($sort);
        $query = Blog::query()->with('tags');

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

        // search across title and slug
        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', $q)
                    ->orWhere('slug', 'like', $q);
            });
        }

        // published filter
        if ($filters['published_at'] !== null) {
            if ($filters['published_at'] === true) {
                $query->whereNotNull('published_at');
            } else {
                $query->whereNull('published_at');
            }
        }

        // tag filter via Spatie Tags (match by name)
        if ($filters['tag']) {
            $query->withAnyTags([$filters['tag']]);
        }

        // whitelist sortable columns
        $sortable = ['title', 'published_at', 'created_at'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'created_at';
        $dir = $sort['dir'] === 'asc' ? 'asc' : 'desc';

        // Sorting
        if ($by === 'published_at') {
            // Place NULLs last for both directions by sorting on IS NULL first, then by date
            // In MySQL/SQLite, FALSE < TRUE, so this pushes non-nulls (0) before nulls (1)
            $query->orderByRaw('published_at IS NULL ASC')
                  ->orderBy('published_at', $dir);
        } elseif ($by === 'title') {
            // Case-insensitive sort in a portable way
            $query->orderByRaw('LOWER(title) ' . ($dir === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $query->orderBy($by, $dir);
        }

        $blogs = $query->paginate(15)->withQueryString();
        return Inertia::render('Admin/Blog/Index', [
            'blogs' => $blogs,
            'filters' => $filters,
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Blog/Edit', [
            'blog' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $blog = Blog::create($data);
        if (!empty($data['tags'])) {
            $blog->syncTags($data['tags']);
        }
        return redirect()->route('admin.blogs.index')->with('success', 'Blog created');
    }

    public function edit(Blog $blog): Response
    {
        $blog->load('tags');
        // Ensure published_at is serialized for Inertia
        if ($blog->published_at) {
            $blog->published_at = $blog->published_at->toIso8601String();
        }
        return Inertia::render('Admin/Blog/Edit', [
            'blog' => $blog,
        ]);
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $this->validateData($request, $blog->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $blog->update($data);
        if (array_key_exists('tags', $data)) {
            $blog->syncTags($data['tags'] ?? []);
        }
        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $blog->delete();
        return back()->with('success', 'Blog deleted');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($id)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
        ]);
    }
}
