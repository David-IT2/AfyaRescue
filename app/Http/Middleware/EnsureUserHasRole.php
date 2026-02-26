<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Comma-separated roles from route (e.g. "hospital_admin,super_admin")
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('login'));
        }

        $allowed = collect($roles)->flatMap(fn (string $r) => explode(',', $r))->map(fn (string $r) => trim($r))->filter()->unique()->values()->all();

        if (! $request->user()->hasRole(...$allowed)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden. Required role: ' . implode(' or ', $allowed)], 403)
                : abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
