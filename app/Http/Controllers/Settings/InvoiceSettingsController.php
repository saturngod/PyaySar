<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceSettingsController extends Controller
{
    public function edit()
    {
        $preference = Auth::user()->preference ?? new UserPreference;

        return Inertia::render('settings/invoice', [
            'preference' => $preference,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'default_note' => 'nullable|string',
            'default_bank_account_info' => 'nullable|string',
        ]);

        $user = Auth::user();
        $preference = $user->preference ?? new UserPreference(['user_id' => $user->id]);

        $preference->fill($data);
        $preference->save();

        return redirect()->back();
    }
}
