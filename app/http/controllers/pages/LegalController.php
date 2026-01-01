<?php

namespace App\http\controllers\pages;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacy(): Response
    {
        return Inertia::render('Privacy');
    }

    public function terms(): Response
    {
        return Inertia::render('Terms');
    }

    public function cookie(): Response
    {
        return Inertia::render('Cookie');
    }
}
