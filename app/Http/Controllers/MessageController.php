<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('view', $ticket);

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'is_internal_note' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $isInternal = $user->isStaff() && $request->boolean('is_internal_note');

        $ticket->messages()->create([
            'author_id' => $user->id,
            'body' => $validated['body'],
            'is_internal_note' => $isInternal,
        ]);

        if ($ticket->status === 'resolved' && ! $isInternal) {
            $ticket->update(['status' => 'open']);
        }

        return redirect()->route('tickets.show', $ticket)->with('status', 'Reply posted.');
    }
}
