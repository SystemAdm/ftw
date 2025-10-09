<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /**
     * Returns a QR code PNG for the authenticated user.
     * The QR encodes an encrypted string (user id encrypted with APP_KEY),
     * which can later be decrypted back to the original user id.
     */
    public function user(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $encrypted = Crypt::encryptString((string) $user->id);

        $svg = QrCode::format('svg')
            ->size(512)
            ->margin(1)
            ->generate($encrypted);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
