<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeamContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($team = $request->route('team')) {
            $teamId = is_numeric($team) ? $team : ($team->id ?? 0);
            setPermissionsTeamId($teamId);
        } else {
            setPermissionsTeamId(0);
        }

        return $next($request);
    }
}
