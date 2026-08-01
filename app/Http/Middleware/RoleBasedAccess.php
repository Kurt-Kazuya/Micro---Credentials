<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($this->shouldAllowGuestAccess($request)) {
                return $next($request);
            }

            return redirect()->route('login');
        }

        $roleId = (int) (Auth::user()->role_id ?? 3);
        $requiredRole = strtolower($roles[0] ?? 'student');

        if (! $this->isAllowedRole($roleId, $requiredRole)) {
            return redirect()->route($this->dashboardRouteForRole($roleId));
        }

        return $next($request);
    }

    protected function shouldAllowGuestAccess(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return in_array($routeName, [
            'admin.dashboard',
            'admin.profile',
            'admin.usermanagement',
            'admin.courses',
            'admin.report',
            'faculty.dashboard',
            'faculty.courses',
            'faculty.create',
            'faculty.analytics',
            'dashboard',
            'courses.browse',
            'profile.show',
            'pathways.index',
            'analytics.index',
            'badges.index',
            'certificates.index',
        ], true);
    }

    protected function isAllowedRole(int $roleId, string $requiredRole): bool
    {
        return match ($requiredRole) {
            'admin' => $roleId === 1,
            'faculty' => $roleId === 2,
            'student' => $roleId === 3,
            default => false,
        };
    }

    protected function dashboardRouteForRole(int $roleId): string
    {
        return match ($roleId) {
            1 => 'admin.dashboard',
            2 => 'faculty.dashboard',
            default => 'dashboard',
        };
    }
}
