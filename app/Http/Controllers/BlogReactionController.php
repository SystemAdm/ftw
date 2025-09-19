<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogReactionController extends Controller
{

    public function react(Request $request, Blog $blog): RedirectResponse
    {

        $data = $request->validate([
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        $type = $data['type'] ?? 'like';

        $request->user()->reactTo($blog, $type);

        return back();
    }

    public function unreact(Request $request, Blog $blog): RedirectResponse
    {
        $request->user()->removeReactionFrom($blog);

        return back();
    }
}
