<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LicenseController extends Controller
{
    protected $disk = 'public';
    protected $licensePath = 'license/license.json';
    protected $keyPath = 'license/public.pem';

    public function show()
    {
        return view('pages.super_admin.license.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'license' => 'required|file|mimes:json'
        ]);

        $disk = Storage::disk($this->disk);

        // Ensure directory exists
        if (!$disk->exists('license')) {
            $disk->makeDirectory('license');
        }

        $content = file_get_contents($request->file('license'));

        // Optional: validate JSON
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON file');
        }

        $disk->put($this->licensePath, $content);

        // 🔥 clear cache (IMPORTANT)
        cache()->forget('license.validation');

        return redirect('/')->with('success', 'License uploaded successfully');
    }

    public function uploadKey(Request $request)
    {
        $request->validate([
            'key' => 'required|file'
        ]);

        $disk = Storage::disk($this->disk);

        if (!$disk->exists('license')) {
            $disk->makeDirectory('license');
        }

        $disk->put($this->keyPath, file_get_contents($request->file('key')));

        // 🔥 clear cache
        cache()->forget('license.validation');

        return back()->with('success', 'Public key uploaded');
    }

    public function delete(Request $request)
    {
        $type = $request->type;
        $disk = Storage::disk($this->disk);

        if ($type === 'license') {
            if ($disk->exists($this->licensePath)) {
                $disk->delete($this->licensePath);
            }
        }

        if ($type === 'key') {
            if ($disk->exists($this->keyPath)) {
                $disk->delete($this->keyPath);
            }
        }

        // 🔥 clear cache
        cache()->forget('license.validation');

        return back()->with('success', ucfirst($type) . ' deleted');
    }
}