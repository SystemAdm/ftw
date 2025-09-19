<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function store(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $blog->comments()->create([
            'user_id' => $request->user()?->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comment added');
    }

    public function destroy(Request $request, Blog $blog, Comment $comment): RedirectResponse
    {
        // Ensure the comment belongs to this blog
        if ($comment->commentable_type !== Blog::class || $comment->commentable_id !== $blog->id) {
            abort(404);
        }
        // Authorize: only comment owner can delete
        if ($comment->user_id !== $request->user()->id) {
            abort(403);
        }
        $comment->delete();
        return back()->with('success', 'Comment removed');
    }
}
