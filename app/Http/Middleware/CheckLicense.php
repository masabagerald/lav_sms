<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function handle(Request $request, Closure $next)
    {
        // ✅ 1. Allow license-related routes (avoid redirect loop)
        if ($this->isLicenseRoute($request)) {
            return $next($request);
        }

        // ✅ 2. Optional: Skip in local environment
        if (app()->environment('local')) {
            return $next($request);
        }

        // ✅ 3. Cache result (avoid heavy validation on every request)
        $result = cache()->remember('license.validation', now()->addMinutes(5), function () {
            return $this->licenseService->validate();
        });

        // 🧠 Debug logging (optional but helpful)
        if (!$result['valid']) {
            Log::warning('License middleware blocked request', [
                'code' => $result['code'] ?? null,
                'message' => $result['message'] ?? null,
                'url' => request()->fullUrl()
            ]);
        }

        // ✅ 4. Handle missing key
        if (($result['code'] ?? null) === 'KEY_MISSING') {
            return redirect()
                ->route('license.upload')
                ->with('license_error', '⚠️ Public key missing. Please upload it.');
        }

        // ✅ 5. Handle invalid license
        if (!$result['valid']) {
            return redirect()
                ->route('license.upload')
                ->with('license_error', $result['message'] ?? 'License error');
        }

        return $next($request);
    }

    protected function isLicenseRoute(Request $request): bool
    {
        return $request->is('license*') 
            || $request->routeIs('license.*');
    }
}