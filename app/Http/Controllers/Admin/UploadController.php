<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    /**
     * Handle image upload for admin forms.
     */
    public function storeImage(Request $request): Response
    {
        $data = $request->validate([
            'image' => ['required', File::image()->max('5mb')],
            'folder' => ['nullable', 'string'], // optional subfolder like 'events'
        ]);

        /** @var UploadedFile $file */
        $file = $data['image'];
        $folder = trim($data['folder'] ?: 'uploads', '/');

        $path = $file->storePublicly($folder, ['disk' => 'public']);
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'path' => '/storage/' . ltrim($path, '/'),
            'url' => $url,
        ]);
    }
}
