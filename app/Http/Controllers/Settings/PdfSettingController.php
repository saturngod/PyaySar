<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class PdfSettingController extends Controller
{
    public function edit(): Response
    {
        $preference = Auth::user()->preference ?? new UserPreference;

        // List fonts from storage/app/private/fonts
        $fontsPath = storage_path('app/private/fonts');
        $fonts = [];

        if (File::exists($fontsPath)) {
            $files = File::files($fontsPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'ttf') {
                    $fonts[] = $file->getFilename();
                }
            }
        }

        return Inertia::render('settings/pdf', [
            'preference' => $preference,
            'fonts' => $fonts,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pdf_footer_message' => ['nullable', 'string', 'max:500'],
            'pdf_paper_size' => ['required', 'in:a4,letter,legal'],
            'pdf_font' => ['nullable', 'string'],
            'pdf_primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $user = Auth::user();
        $preference = $user->preference ?? new UserPreference(['user_id' => $user->id]);

        $preference->fill($data);
        $preference->save();

        return redirect()->back()->with('success', 'PDF settings updated successfully.');
    }
}
