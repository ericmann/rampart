<?php

namespace App\Http\Controllers;

use App\Models\SavedView;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SavedViewController extends Controller
{
    public function index(Request $request): View
    {
        $views = $request->user()->savedViews()->latest()->get();

        return view('saved-views.index', ['views' => $views]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
        ]);

        $preferences = [
            'status' => $validated['status'] ?? null,
            'priority' => $validated['priority'] ?? null,
        ];

        $request->user()->savedViews()->create([
            'name' => $validated['name'],
            'preferences' => serialize($preferences),
        ]);

        return redirect()->route('saved-views.index')->with('status', 'View saved.');
    }

    public function show(SavedView $savedView): View
    {
        Gate::authorize('view', $savedView);

        // A08:2025 — Software & Data Integrity Failures. `unserialize()` on data that
        // crossed a trust boundary lets an attacker who can get any serialized string into
        // this column instantiate an arbitrary autoloadable class with attacker-controlled
        // properties — object injection. The stored format has to stay `serialize()`
        // (SavedViewTest, the public suite, already asserts against it), so instead of
        // switching formats we pass `allowed_classes => false`: every object in the string
        // is converted to a harmless `__PHP_Incomplete_Class` with no constructor,
        // `__wakeup()`, or `__destruct()` ever invoked, while plain arrays/scalars — the
        // only shapes this feature actually needs — still decode normally.
        $preferences = unserialize($savedView->preferences, ['allowed_classes' => false]);

        return view('saved-views.show', [
            'savedView' => $savedView,
            'preferences' => is_array($preferences) ? $preferences : [],
        ]);
    }

    public function destroy(SavedView $savedView): RedirectResponse
    {
        Gate::authorize('delete', $savedView);

        $savedView->delete();

        return redirect()->route('saved-views.index')->with('status', 'View removed.');
    }
}
