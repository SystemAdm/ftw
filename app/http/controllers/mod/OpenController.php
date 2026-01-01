<?php

namespace App\http\controllers\mod;

use App\http\controllers\Controller;
use App\models\BuildingInside;
use App\models\BuildingLog;
use Inertia\Inertia;
use Inertia\Response;

class OpenController extends Controller
{
    public function index(): Response
    {
        $inside = BuildingInside::with('user')
            ->orderBy('entered_at', 'desc')
            ->get();

        $history = BuildingLog::with('user')
            ->where('created_at', '>=', now()->subWeek())
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return Inertia::render('mod/Open', [
            'inside' => $inside,
            'history' => $history,
        ]);
    }
}
