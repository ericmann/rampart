<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('view', $ticket);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $storedName = Str::uuid().'-'.$file->getClientOriginalName();
        $path = $file->storeAs('attachments', $storedName, 'local');

        Attachment::create([
            'ticket_id' => $ticket->id,
            'uploader_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        Gate::authorize('view', $attachment->ticket);

        abort_unless(Storage::disk('local')->exists($attachment->stored_path), 404);

        return Storage::disk('local')->download($attachment->stored_path, $attachment->original_name);
    }
}
