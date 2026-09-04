<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Support\Authorization;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = $user->isStaff()
            ? Ticket::query()
            : Ticket::query()->where('requester_id', $user->id);

        // A05:2025 — Injection. The old implementation built the search query with raw
        // string concatenation (`... LIKE '%".$q."%' ORDER BY {$sort} ...`), so `q` and
        // `sort` were both directly injectable SQL. Eloquent's query builder parameter-
        // binds every value below, so injection metacharacters are treated as literal
        // search text instead of SQL syntax. The free-form `sort` column is gone entirely
        // — the index only ever sorts by `created_at` — since there was no legitimate need
        // to accept an arbitrary column/direction from the client at all.
        if ($request->filled('q')) {
            $query->where('subject', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $tickets = $query->with(['requester', 'assignedAgent'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tickets.index', ['tickets' => $tickets, 'q' => $request->input('q', '')]);
    }

    public function create(): View
    {
        return view('tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $user = $request->user();

        $ticket = Ticket::create([
            'organization_id' => $user->organization_id,
            'requester_id' => $user->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket filed.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        // A01:2025 — Broken Access Control (IDOR). This action previously loaded and
        // rendered any ticket by id with no ownership/role check at all — a route being
        // reachable is not authorization. TicketPolicy::view() already encodes the correct
        // rule (staff see everything, a customer only their own); this was the one place
        // that never called it.
        Gate::authorize('view', $ticket);

        $ticket->load(['requester', 'assignedAgent', 'attachments']);

        // A06:2025 — Insecure Design. This used to default to isStaff() but let a
        // `can_view_internal_notes=1` query/form value override it — an authorization
        // decision must never be accepted as request input; it has to be recomputed
        // server-side every time, with nothing the client sends able to influence it.
        $canViewInternalNotes = $request->user()->isStaff();

        $messages = $ticket->messages()
            ->when(! $canViewInternalNotes, fn ($query) => $query->where('is_internal_note', false))
            ->with('author', 'attachments')
            ->get();

        return view('tickets.show', [
            'ticket' => $ticket,
            'messages' => $messages,
            'canViewInternalNotes' => $canViewInternalNotes,
            'agents' => User::where('role', User::ROLE_AGENT)->get(),
        ]);
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        if (! $request->user()->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,resolved,closed'],
        ]);

        $ticket->update(['status' => $validated['status']]);

        return back()->with('status', 'Ticket updated.');
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $agentId = $request->input('agent_id');

        if (! Authorization::allows($request->user(), 'assign', [$ticket, $agentId])) {
            abort(403);
        }

        $ticket->update(['assigned_agent_id' => is_numeric($agentId) ? (int) $agentId : null]);

        return back()->with('status', 'Ticket reassigned.');
    }
}
