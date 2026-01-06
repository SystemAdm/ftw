<?php

namespace App\Http\Middleware;

use App\Enums\RolesEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCrew
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request); // Let 'auth' middleware handle it
        }

        // Check if user has global admin roles or any crew-related role in any team
        $isCrew = RolesEnum::userIsGlobalAdmin($user) ||
            \DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', get_class($user))
                ->exists();

        if (! $isCrew) {
            \Log::info('EnsureIsCrew: User is not crew', ['user_id' => $user->id, 'email' => $user->email]);
            abort(403);
        }

        return $next($request);
    }
}
