<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                'qrEncryptedUserId' => $user ? Crypt::encryptString((string) $user->id) : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'status' => fn () => $request->session()->get('status'),
            'error' => fn () => $request->session()->get('error'),
            // Expose environment flag so we can hide experimental UI in production
            'isProduction' => app()->isProduction(),
            // Minimal i18n payload for public + settings UI
            'i18n' => [
                'locale' => app()->getLocale(),
                'fallback' => config('app.fallback_locale'),
                'trans' => [
                    'ui' => trans('ui'),
                    // Legal and other page copy
                    'pages' => trans('pages'),
                ],
            ],
        ];
    }
}
