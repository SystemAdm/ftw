<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Location;
use App\Models\PostalCode;
use App\Models\Team;
use App\Models\User;
use App\Models\Weekday;
use DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(): Response
    {
        setPermissionsTeamId(0);

        return Inertia::render('admin/Dashboard', [
            'counts' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'permissions' => Permission::count(),
                'teams' => Team::count(),
                'postcodes' => PostalCode::count(),
                'locations' => Location::count(),
                'weekdays' => Weekday::count(),
                'events' => Event::count(),
                'relations' => DB::table('guardian_user')->count(),
            ],
        ]);
    }
}
