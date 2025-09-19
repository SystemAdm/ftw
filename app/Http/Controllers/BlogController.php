<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $blogs = Blog::query()
            ->with('tags')
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Blog/Index', [
            'blogs' => $blogs,
        ]);
    }

    public function show(Blog $blog): Response
    {
        $blog->load(['tags', 'comments' => fn($q) => $q->latest()->with('user')]);

        // basic reaction summary and current user's reactions
        $summary = $blog->reactions()
            ->select('type')
            ->selectRaw('count(*) as count')
            ->groupBy('type')
            ->get();

        $myReactions = auth()->check()
            ? $blog->reactions()->where('user_id', auth()->id())->pluck('type')
            : collect();

        return Inertia::render('Blog/Show', [
            'blog' => $blog,
            'reactions' => [
                'summary' => $summary,
                'mine' => $myReactions,
            ],
        ]);
    }
}
