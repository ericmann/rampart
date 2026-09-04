<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KbArticle;
use App\Support\UrlFetcher;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KbController extends Controller
{
    public function index(): View
    {
        $articles = KbArticle::where('is_published', true)->latest()->paginate(15);

        return view('kb.index', ['articles' => $articles]);
    }

    public function show(KbArticle $article): View
    {
        abort_unless($article->is_published || request()->user()?->isStaff(), 404);

        return view('kb.show', ['article' => $article]);
    }

    public function create(): View
    {
        return view('kb.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $article = KbArticle::create([
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(6),
            'body' => $validated['body'],
            'is_published' => true,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'kb_article.created',
            'context' => ['article_id' => $article->id],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('kb.show', $article)->with('status', 'Article published.');
    }

    /**
     * Fetches the pasted URL server-side so the author can preview it before citing it.
     */
    public function previewLink(Request $request, UrlFetcher $fetcher): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string'],
        ]);

        $body = $fetcher->fetch($validated['url']);

        return response()->json([
            'url' => $validated['url'],
            'preview' => Str::limit($body, 4000),
        ]);
    }
}
