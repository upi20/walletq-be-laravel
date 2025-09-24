<?php

namespace App\Http\Controllers\API\User\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = Tag::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
        ]);

        return response()->json($tag, 201);
    }

    public function show(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($tag);
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if tag is used in any transactions
        if ($tag->transactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete tag that is used in transactions'
            ], 422);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}