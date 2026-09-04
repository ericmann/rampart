<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Read-only "here's what's configured" screen for the workshop — deliberately surfaces the
 * misconfigurations (debug mode, CORS, default admin) rather than hiding them, so attendees
 * can see the blast radius from the admin's own point of view. See docs/VULN-MAP.md (A02).
 */
class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'appDebug' => config('app.debug'),
            'appEnv' => config('app.env'),
            'corsPaths' => config('cors.paths'),
            'corsAllowedOrigins' => config('cors.allowed_origins'),
        ]);
    }
}
