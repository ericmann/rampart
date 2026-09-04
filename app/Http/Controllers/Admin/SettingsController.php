<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Read-only "here's what's configured" screen for admins.
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
