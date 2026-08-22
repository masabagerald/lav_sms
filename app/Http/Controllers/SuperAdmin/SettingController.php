<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\Qm;
use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingUpdate;
use App\Models\ActivityLog;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $setting, $my_class;

    public function __construct(SettingRepo $setting, MyClassRepo $my_class)
    {
        $this->setting = $setting;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $s = $this->setting->all();
        $d['class_types'] = $this->my_class->getTypes();
        $d['s'] = $s->flatMap(function ($s) {
            return [$s->type => $s->description];
        });

        // ---- Module management ----
        $d['modules'] = Qm::all();
        $d['grouped_modules'] = Qm::groupedByCategory();
        $d['disabled_modules'] = Qm::disabledSlugs();
        $d['activity_logs'] = ActivityLog::with('user')->latest()->take(8)->get();

        return view('pages.super_admin.settings', $d);
    }

    public function update(SettingUpdate $req)
    {
        $sets = $req->except('_token', '_method', 'logo');
        $sets['lock_exam'] = $sets['lock_exam'] == 1 ? 1 : 0;
        $keys = array_keys($sets);
        $values = array_values($sets);
        for ($i = 0; $i < count($sets); $i++) {
            $this->setting->update($keys[$i], $values[$i]);
        }

        if ($req->hasFile('logo')) {
            $logo = $req->file('logo');
            $f = Qs::getFileMetaData($logo);
            $f['name'] = 'logo.' . $f['ext'];
            $f['path'] = $logo->storeAs(Qs::getPublicUploadPath(), $f['name']);
            $logo_path = asset('storage/' . $f['path']);
            $this->setting->update('logo', $logo_path);
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Enable / disable a system module.
     *
     * Disabling NEVER deletes data — it only revokes availability (menu + routes).
     * If enabled modules depend on the target module, the request is rejected with
     * HTTP 409 unless "force" is set (the UI asks for explicit confirmation first).
     */
    public function toggleModule(Request $req)
    {
        $data = $req->validate([
            'slug'  => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        $module = Qm::get($data['slug']);

        if (!$module) {
            return response()->json(['ok' => false, 'msg' => 'Unknown module.'], 404);
        }

        if (!empty($module['required'])) {
            return response()->json([
                'ok' => false,
                'msg' => "{$module['name']} is a required core module and cannot be disabled.",
            ], 422);
        }

        $wasEnabled = Qm::enabled($data['slug']);

        // Guard dependencies when disabling
        if ($wasEnabled && empty($data['force'])) {
            $activeDependents = Qm::activeDependents($data['slug']);

            if ($activeDependents) {
                $names = collect($activeDependents)->map(function ($s) {
                    return Qm::get($s)['name'] ?? $s;
                })->implode(', ');

                return response()->json([
                    'ok'          => false,
                    'code'        => 'dependent_modules',
                    'msg'         => "{$module['name']} cannot be disabled because the following modules depend on it: {$names}.",
                    'dependents'  => $names,
                ], 409);
            }
        }

        // Persist new state (data is never touched — access only)
        $slugs = Qm::disabledSlugs();
        if ($wasEnabled) {
            $slugs[] = $data['slug'];
        } else {
            $slugs = array_values(array_diff($slugs, [$data['slug']]));
        }
        Qm::persistDisabled($slugs);

        $newState = $wasEnabled ? 'disabled' : 'enabled';

        ActivityLog::record(
            "module.{$newState}",
            ($wasEnabled ? 'Disabled' : 'Enabled') . " the {$module['name']} module",
            [
                'module'      => $data['slug'],
                'previous'    => $wasEnabled ? 'enabled' : 'disabled',
                'new'         => $newState,
                'forced'      => (bool) ($data['force'] ?? false),
            ]
        );

        return response()->json([
            'ok'      => true,
            'msg'     => "{$module['name']} module {$newState}.",
            'state'   => $newState,
            'slug'    => $data['slug'],
        ]);
    }

    /** Recent system activity (JSON, used by the Modules tab refresh) */
    public function activity()
    {
        return response()->json([
            'ok'  => true,
            'log' => ActivityLog::with('user')->latest()->take(8)->get()
                ->map(function ($l) {
                    return [
                        'action' => $l->action,
                        'description' => $l->description,
                        'by' => optional($l->user)->name,
                        'at' => $l->created_at->format('d M Y, H:i'),
                    ];
                }),
        ]);
    }
}
