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
     * Raw query so we can build the sort/scope clauses dynamically without a big
     * conditional chain of Eloquent builder calls.
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

    public function show(Request $request, Ticket $ticket): View
    {
        $ticket->load(['requester', 'assignedAgent', 'attachments']);

        // Defaults from the current user's role, but the reply form round-trips this
        // value so agents can toggle note visibility without a page reload.
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
