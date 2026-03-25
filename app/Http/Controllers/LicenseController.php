<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function show()
    {

    
        return view('pages.super_admin.license.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'license' => 'required|file'
        ]);

        $path = storage_path('app/license/license.json');

        file_put_contents($path, file_get_contents($request->file('license')));

        return redirect('/')->with('success', 'License uploaded successfully');
    }

    public function uploadKey(Request $request)
{
    $request->validate([
        'key' => 'required|file'
    ]);

    $path = storage_path('app/license/public.pem');

    file_put_contents($path, file_get_contents($request->file('key')));

    return back()->with('success', 'Public key uploaded');
}

public function delete(Request $request)
{
    $type = $request->type;

    if ($type === 'license') {
        @unlink(storage_path('app/license/license.json'));
    }

    if ($type === 'key') {
        @unlink(storage_path('app/license/public.pem'));
    }

    return back()->with('success', ucfirst($type) . ' deleted');
}
}