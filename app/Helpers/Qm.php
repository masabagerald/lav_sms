<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Module Management helper.
 *
 * Reads the registry from config/modules.php and resolves each module's
 * enabled/disabled state from the persisted `settings` table
 * (type = "disabled_modules", description = JSON array of disabled slugs).
 * Resolved state is cached; call Qm::flushCache() after changing state.
 */
class Qm
{
    public const SETTING_KEY = 'disabled_modules';
    public const CACHE_KEY   = 'qm.disabled_modules';

    /** Raw registry array; falls back to the file when config cache is stale */
    protected static function registry(): array
    {
        static $fallback = null;

        $modules = config('modules');

        if (!is_array($modules) || !count($modules)) {
            if ($fallback === null) {
                $file = config_path('modules.php');
                $fallback = file_exists($file) ? (array) require $file : [];
            }
            $modules = $fallback;
        }

        return is_array($modules) ? $modules : [];
    }

    /** All registered modules keyed by slug */
    public static function all(): Collection
    {
        return collect(self::registry());
    }

    /** Single module definition or null */
    public static function get(string $slug)
    {
        return self::registry()[$slug] ?? null;
    }

    /** Slugs of currently disabled modules (persisted, cached) */
    public static function disabledSlugs(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            $row = Setting::where('type', self::SETTING_KEY)->first();

            if (!$row || !$row->description) {
                return [];
            }

            $decoded = json_decode($row->description, true);

            return is_array($decoded) ? array_values($decoded) : [];
        });
    }

    /** Is the given module enabled? Required modules are always enabled. */
    public static function enabled(string $slug): bool
    {
        $module = self::get($slug);

        if (!$module || ($module['required'] ?? false)) {
            return true;
        }

        return !in_array($slug, self::disabledSlugs(), true);
    }

    /**
     * Resolve the module owning the given route name.
     * The most specific (longest) matching prefix wins, e.g. "students.promotion_manage"
     * resolves to the promotions module, not students.
     */
    public static function findByRouteName(string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $best = null;
        $bestLen = 0;

        foreach (self::all() as $slug => $module) {
            foreach ($module['routes'] ?? [] as $prefix) {
                if (strpos($routeName, $prefix) === 0 && strlen($prefix) > $bestLen) {
                    $best = $slug;
                    $bestLen = strlen($prefix);
                }
            }
        }

        return $best;
    }

    /** Modules (slugs) that depend on the given module */
    public static function dependents(string $slug): array
    {
        return self::all()
            ->filter(function ($module, $s) use ($slug) {
                return in_array($slug, $module['depends_on'] ?? [], true);
            })
            ->keys()
            ->all();
    }

    /** Dependents of $slug that are currently ENABLED */
    public static function activeDependents(string $slug): array
    {
        return array_values(array_filter(
            self::dependents($slug),
            function ($s) {
                return self::enabled($s);
            }
        ));
    }

    /** Persist new disabled-slugs list */
    public static function persistDisabled(array $slugs): void
    {
        Setting::updateOrCreate(
            ['type' => self::SETTING_KEY],
            ['description' => json_encode(array_values(array_unique($slugs)))]
        );

        self::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** Modules grouped by category for the management grid (each entry carries its slug) */
    public static function groupedByCategory(): Collection
    {
        return self::all()
            ->map(function ($module, $slug) {
                $module['slug'] = $slug;

                return $module;
            })
            ->groupBy(function ($module) {
                return $module['category'] ?? 'Other';
            })
            ->sortKeys();
    }
}
