<?php

namespace App\Http\Controllers\Crew;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('crew/Dashboard', [
            'myTeamsCount' => $user->teams()->wherePivot('status', 'approved')->count(),
            'pendingApplicationsCount' => $user->teams()->wherePivot('status', 'pending')->count(),
        ]);
    }
}
