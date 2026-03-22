<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Only allow users with the 'user' role through.
     * IT staff and admins use the /helpdesk Filament panel instead.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== UserRole::User) {
            abort(403, 'This area is for regular employees only. IT staff should use /helpdesk.');
        }

        return $next($request);
    }
}
