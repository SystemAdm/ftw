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

    /**
     * List previously uploaded images in a given folder.
     */
    public function listImages(Request $request): Response
    {
        $folder = trim((string) $request->query('folder', 'uploads'), '/');
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        // Get files within the folder (non-recursive). Adjust to files($folder, true) for recursive if needed
        $files = collect(Storage::disk('public')->files($folder))
            ->filter(function ($path) use ($extensions) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                return in_array($ext, $extensions);
            })
            ->values()
            ->map(function ($path) {
                return [
                    'path' => '/storage/' . ltrim($path, '/'),
                    'url' => Storage::disk('public')->url($path),
                    'filename' => basename($path),
                ];
            });

        return response()->json([
            'data' => $files,
        ]);
    }
}
