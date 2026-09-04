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
            // Written with serialize(), not json_encode() — see show() below.
            'preferences' => serialize($preferences),
        ]);

        return redirect()->route('saved-views.index')->with('status', 'View saved.');
    }

    /**
     * unserialize() on a blob that came from the `preferences` column with no
     * `allowed_classes` restriction. Any class autoloadable by the app can be instantiated
     * via a crafted serialized payload, running its __wakeup()/__destruct(). See
     * docs/VULN-MAP.md (A08) and App\Support\SavedViewGadget for the planted, safe-payload
     * gadget class used to prove it.
     */
    public function show(SavedView $savedView): View
    {
        Gate::authorize('view', $savedView);

        $preferences = unserialize($savedView->preferences);

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
