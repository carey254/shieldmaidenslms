<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow any authenticated user to access student routes
        // This allows admins and instructors to test student functionality
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized. Authentication required.'], 403);
        }

        return $next($request);
    }
}
