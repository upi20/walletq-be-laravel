<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TagController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $tags = Tag::where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Settings/Tags/Index', [
            'tags' => $tags,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Tags/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        Tag::create($validated);

        return redirect()
            ->route('settings.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function show(Tag $tag)
    {
        $this->authorize('view', $tag);

        return Inertia::render('Settings/Tags/Show', [
            'tag' => $tag,
        ]);
    }

    public function edit(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat mengubah tag milik orang lain.');
        }

        return Inertia::render('Settings/Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat mengubah tag milik orang lain.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag->update($validated);

        return redirect()
            ->route('settings.tags.index')
            ->with('success', 'Tag berhasil diperbarui.');
    }

    public function destroy(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus tag milik orang lain.');
        }

        $tag->delete();

        return redirect()
            ->route('settings.tags.index')
            ->with('success', 'Tag berhasil dihapus.');
    }
}
