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
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
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
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

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
        $this->authorize('update', $tag);

        return Inertia::render('Settings/Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? $tag->is_active;

        $tag->update($validated);

        return redirect()
            ->route('settings.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        // Check if tag has transactions
        if ($tag->transactions()->exists()) {
            return back()->with('error', 'Cannot delete tag that has transactions.');
        }

        $tag->delete();

        return redirect()
            ->route('settings.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}