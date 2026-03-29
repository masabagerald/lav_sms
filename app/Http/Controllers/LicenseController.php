<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    // Ensure directory exists
    Storage::makeDirectory('license');

    // Store file
    Storage::put('license/license.json', file_get_contents($request->file('license')));

    return redirect('/')->with('success', 'License uploaded successfully');
}


public function uploadKey(Request $request)
{
    $request->validate([
        'key' => 'required|file'
    ]);

    Storage::makeDirectory('license');    

    Storage::put('license/public.pem', file_get_contents($request->file('key')));

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