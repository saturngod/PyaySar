<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserPreferenceController extends Controller
{
    public function edit()
    {
        $preference = Auth::user()->preference ?? new UserPreference;

        return Inertia::render('settings/preference', [
            'preference' => $preference,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string',
            'company_logo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $user = Auth::user();
        $preference = $user->preference ?? new UserPreference(['user_id' => $user->id]);

        if ($request->hasFile('company_logo')) {
            // Delete old logo if exists
            if ($preference->company_logo) {
                Storage::disk('public')->delete($preference->company_logo);
            }
            $path = $request->file('company_logo')->store('logos', 'public');
            $data['company_logo'] = $path;
        }

        $preference->fill($data);
        $preference->save();

        return redirect()->back(); // or to route('preferences.edit')
    }
}
