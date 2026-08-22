<?php

namespace App\Http\Middleware\Custom;

use App\Helpers\Qm;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Blocks access to routes that belong to a disabled module.
 *
 * The module is resolved from the current ROUTE NAME using the prefixes
 * registered in config/modules.php, so every current and future route is
 * protected automatically without touching individual route definitions.
 *
 * Disabled = unavailable for everyone (including admins). Super Admins manage
 * modules from Settings > Modules, which is not part of any toggleable module.
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request); // guests are handled by auth middleware
        }

        // The Module Management console itself must always be reachable
        if ($request->routeIs(['settings.*', 'settings'])) {
            return $next($request);
        }

        $slug = Qm::findByRouteName($request->route() ? $request->route()->getName() : '');

        if (!$slug || Qm::enabled($slug)) {
            return $next($request);
        }

        $module = Qm::get($slug);
        $name = $module['name'] ?? ucfirst($slug);
        $message = "The {$name} module is currently disabled. Contact your administrator to enable it.";

        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'msg' => $message, 'module' => $slug], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('flash_danger', $message);
    }
}
