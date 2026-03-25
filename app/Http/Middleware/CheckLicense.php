<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;

class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
   
      
        $licenseService = new LicenseService();
        $result = $licenseService->validate();

        // Allow license routes
        if ($request->is('license*')) {
            return $next($request);
        }

        $code = $result['code'] ?? null;

        // KEY missing
        if ($code === 'KEY_MISSING') {
            return redirect()
                ->route('license.upload')
                ->with('license_error', '⚠️ Public key missing. Please upload it.');
        }

        // Any invalid license
        if (!$result['valid']) {
            return redirect()
                ->route('license.upload')
                ->with('license_error', $result['message'] ?? 'License error');
        }

        return $next($request);
    
    }
}
