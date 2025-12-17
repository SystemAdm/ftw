<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacy(): Response
    {
        return Inertia::render('privacy');
    }

    public function terms(): Response
    {
        return Inertia::render('terms');
    }

    public function cookie(): Response
    {
        return Inertia::render('cookie');
    }
}
