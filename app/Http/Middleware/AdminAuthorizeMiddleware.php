<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthorizeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        if (auth()->guard('admin')->user()->role_id ==null) {
            return $next($request);
        }


        // Get the current route name
        $routeName = \Route::currentRouteName();

        // Get the permissions of the authenticated admin user
        $permissions = auth()->guard('admin')->user()->role->permission ?? null;

        // Retrieve the access list from the configuration and check if the user has the necessary permission
        $allowedRoutes = collect(config('role'))->pluck('access')->flatten()->intersect($permissions);

        // If user has no access to this route
        if (!$allowedRoutes->contains($routeName)) {
            return redirect()->route('admin.403'); // Ensure this returns a Response
        }


        return $next($request); // If the role is null, allow the request to pass


    }
}
