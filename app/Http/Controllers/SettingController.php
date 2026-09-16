<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'address'     => 'nullable|string|max:1000',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:255',
            'currency'    => 'nullable|string|max:10',
        ]);

        // Only the validated keys are written — nothing else can sneak in
        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
