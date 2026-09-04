<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Support\Authorization;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = ['created_at', 'updated_at', 'subject', 'status', 'priority'];

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($request->filled('q')) {
            $tickets = $this->rawSearch($user, (string) $request->string('q'), (string) $request->string('sort', 'created_at'));
        } else {
            $query = $user->isStaff()
                ? Ticket::query()
                : Ticket::query()->where('requester_id', $user->id);

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            }

            $tickets = $query->with(['requester', 'assignedAgent'])
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        return view('tickets.index', ['tickets' => $tickets, 'q' => $request->input('q', '')]);
    }

    /**
     * Ticket search — builds the WHERE clause by string concatenation instead of a bound
     * parameter, and the `sort` column is interpolated directly into ORDER BY with no
     * whitelist check. `' OR '1'='1` returns every ticket; `UNION SELECT ... FROM users`
     * pulls password hashes into the results. See docs/VULN-MAP.md (A05).
     */
    private function rawSearch(User $user, string $q, string $sort): \Illuminate\Support\Collection
    {
        $scope = $user->isStaff() ? '1=1' : 'requester_id = '.(int) $user->id;

        $sql = "SELECT * FROM tickets WHERE ({$scope}) AND subject LIKE '%".$q."%' ORDER BY {$sort} DESC LIMIT 200";

        $rows = DB::select($sql);

        return collect($rows)->map(fn ($row) => Ticket::hydrate([(array) $row])->first());
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

    /**
     * No ownership or policy check at all — any authenticated user who guesses/enumerates
     * a ticket id can read it in full, including staff-only internal notes if they also
     * flip the `can_view_internal_notes` hint (see below). See docs/VULN-MAP.md (A01a).
     */
    public function show(Request $request, Ticket $ticket): View
    {
        $ticket->load(['requester', 'assignedAgent', 'attachments']);

        // The view renders a hidden field carrying this same flag back to the server on
        // some actions; the server should recompute it from $user->isStaff() but instead
        // trusts whatever the client sends. A customer can tamper it to see staff-only
        // internal notes on any ticket. See docs/VULN-MAP.md (A06).
        $canViewInternalNotes = $request->boolean('can_view_internal_notes', $request->user()->isStaff());

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

    /**
     * Reassign a ticket to a different agent. Authorization goes through the fail-open
     * helper — posting a non-existent/non-numeric `agent_id` throws inside the policy and
     * the exception is swallowed into an ALLOW, so even a customer can reassign a ticket
     * by supplying a bad agent id. See docs/VULN-MAP.md (A10).
     */
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
