<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                #{{ $ticket->id }} &middot; {{ $ticket->subject }}
            </h2>
            <x-status-badge :status="$ticket->status" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('shareUrl'))
                <div class="bg-indigo-50 dark:bg-indigo-950 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 text-sm">
                    <p class="font-medium text-indigo-800 dark:text-indigo-200">Share this read-only status link (expires in 7 days):</p>
                    <code class="block mt-2 break-all bg-white dark:bg-gray-900 rounded p-2">{{ session('shareUrl') }}</code>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 grid sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Requester</div>
                    <div class="font-medium">{{ $ticket->requester->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Assigned agent</div>
                    <div class="font-medium">{{ $ticket->assignedAgent->name ?? 'Unassigned' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Priority</div>
                    <div class="font-medium capitalize">{{ $ticket->priority }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('tickets.share', $ticket) }}">
                @csrf
                <button class="text-sm text-indigo-600 hover:text-indigo-500">Get a shareable status link &rarr;</button>
            </form>

            @auth
                @if (auth()->user()->isStaff())
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Status &amp; assignment</h3>
                        <div class="flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                    @foreach (['open','pending','resolved','closed'] as $status)
                                        <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <button class="rounded-md bg-gray-800 text-white text-sm px-3 py-2 hover:bg-gray-700">Update status</button>
                            </form>

                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="agent_id" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                    <option value="">Unassign</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}" @selected($ticket->assigned_agent_id === $agent->id)>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                                <button class="rounded-md bg-gray-800 text-white text-sm px-3 py-2 hover:bg-gray-700">Reassign</button>
                            </form>
                        </div>
                    </div>
                @endif
            @endauth

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
                <div class="p-6">
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $ticket->requester->name ?? '—' }}</span>
                        <span>&middot;</span>
                        <span>{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                    {{-- A05:2025 — Injection (stored XSS). Ticket/message bodies are free-form
                         customer/agent text, not authored HTML, so they're escaped by default
                         with `{{ }}`. Raw `{!! !!}` rendering stays reserved for the KB article
                         body, which is deliberately staff-authored HTML (see kb/show.blade.php)
                         — that's the justified opt-in the checklist calls for, not this. --}}
                    <div class="prose prose-sm dark:prose-invert max-w-none">{{ $ticket->body }}</div>
                </div>

                @foreach ($messages as $message)
                    <div class="p-6 {{ $message->is_internal_note ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}">
                        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $message->author->name ?? '—' }}</span>
                            @if ($message->is_internal_note)
                                <span class="rounded-full bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100 px-2 py-0.5 text-xs">Internal note</span>
                            @endif
                            <span>&middot;</span>
                            <span>{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                            <div class="prose prose-sm dark:prose-invert max-w-none">{{ $message->body }}</div>
                    </div>
                @endforeach
            </div>

            @auth
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Reply</h3>
                    <form method="POST" action="{{ route('tickets.messages.store', $ticket) }}" class="space-y-3">
                        @csrf
                        <textarea name="body" rows="4" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm" required></textarea>
                        <div class="flex items-center justify-between">
                            @if (auth()->user()->isStaff())
                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" name="is_internal_note" value="1" class="rounded border-gray-300">
                                    Internal note (staff only)
                                </label>
                            @else
                                <span></span>
                            @endif
                            <x-primary-button>Post reply</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Attachments</h3>
                    <ul class="space-y-1 mb-4 text-sm">
                        @forelse ($ticket->attachments as $attachment)
                            <li>
                                <a href="{{ route('attachments.download', $attachment) }}" class="text-indigo-600 hover:text-indigo-500">{{ $attachment->original_name }}</a>
                                <span class="text-gray-400">({{ number_format($attachment->size / 1024, 1) }} KB)</span>
                            </li>
                        @empty
                            <li class="text-gray-500">No attachments yet.</li>
                        @endforelse
                    </ul>
                    <form method="POST" action="{{ route('tickets.attachments.store', $ticket) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                        @csrf
                        <input type="file" name="file" required class="text-sm">
                        <button class="rounded-md bg-gray-800 text-white text-sm px-3 py-2 hover:bg-gray-700">Upload</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</x-app-layout>
