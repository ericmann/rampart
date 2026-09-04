<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $base = $user->isStaff() ? Ticket::query() : Ticket::query()->where('requester_id', $user->id);

        $stats = [
            'open' => (clone $base)->where('status', 'open')->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'resolved' => (clone $base)->where('status', 'resolved')->count(),
            'closed' => (clone $base)->where('status', 'closed')->count(),
        ];

        $recentTickets = (clone $base)->with(['requester', 'assignedAgent'])->latest()->take(8)->get();

        return view('dashboard', ['stats' => $stats, 'recentTickets' => $recentTickets]);
    }
}
