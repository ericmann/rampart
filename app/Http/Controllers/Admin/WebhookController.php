<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Support\UrlFetcher;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function index(): View
    {
        return view('admin.webhooks.index', ['webhooks' => Webhook::latest()->get()]);
    }

    public function create(): View
    {
        return view('admin.webhooks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_url' => ['required', 'string', 'max:2048'],
            'event' => ['required', 'string', 'max:255'],
        ]);

        Webhook::create([
            ...$validated,
            'inbound_token' => Str::random(24),
            'secret' => Str::random(40),
            'is_active' => true,
        ]);

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook created.');
    }

    public function edit(Webhook $webhook): View
    {
        return view('admin.webhooks.edit', ['webhook' => $webhook]);
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_url' => ['required', 'string', 'max:2048'],
            'event' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $webhook->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook updated.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $webhook->delete();

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook removed.');
    }

    public function test(Webhook $webhook, UrlFetcher $fetcher): RedirectResponse
    {
        $result = $fetcher->fetch($webhook->target_url);

        $webhook->deliveries()->create([
            'direction' => 'outbound',
            'payload' => ['note' => 'manual test'],
            'result' => Str::limit($result, 500),
        ]);

        return redirect()->route('admin.webhooks.index')->with('status', 'Test sent — see delivery log.');
    }
}
