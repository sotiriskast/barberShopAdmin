<?php


namespace App\Modules\Barber\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarberRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'barber') {
            return response()->json([
                'message' => 'Unauthorized. This endpoint is for barbers only.'
            ], 403);
        }

        return $next($request);
    }
}
