<?php

namespace App\Http\Controllers;

use App\Models\LanguageInsight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageInsightController extends Controller
{
    /**
     * Get insights for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->languageInsights()
            ->orderBy('created_at', 'desc');

        // Filter by read/unread status if requested
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->unread();
        }

        // Optionally limit results
        $limit = $request->input('limit', 10);
        $insights = $query->limit($limit)->get();

        return response()->json([
            'insights' => $insights,
            'unread_count' => Auth::user()->languageInsights()->unread()->count(),
        ]);
    }

    /**
     * Mark an insight as read
     */
    public function markAsRead(LanguageInsight $insight): JsonResponse
    {
        // Ensure user owns this insight
        if ($insight->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $insight->update(['is_read' => true]);

        return response()->json([
            'message' => 'Insight marked as read',
            'insight' => $insight,
        ]);
    }

    /**
     * Mark all insights as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $updated = Auth::user()->languageInsights()
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'All insights marked as read',
            'count' => $updated,
        ]);
    }

    /**
     * Delete/dismiss an insight
     */
    public function destroy(LanguageInsight $insight): JsonResponse
    {
        // Ensure user owns this insight
        if ($insight->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $insight->delete();

        return response()->json([
            'message' => 'Insight deleted',
        ]);
    }
}
