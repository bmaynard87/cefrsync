<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LanguageChatController extends Controller
{
    public function index(Request $request)
    {
        $chatHistory = $request->user()
            ->chatSessions()
            ->latest('last_message_at')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'title' => $session->title ?? 'New Conversation',
                'last_message_at' => $session->last_message_at,
                'native_language' => $session->native_language,
                'target_language' => $session->target_language,
            ]);

        $userSettings = [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ];

        return Inertia::render('LanguageChat', [
            'chatHistory' => $chatHistory,
            'userSettings' => $userSettings,
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'native_language' => 'required|string|max:255',
            'target_language' => 'required|string|max:255',
            'proficiency_level' => 'required|string|max:255',
        ]);

        $session = $request->user()->chatSessions()->create([
            'native_language' => $validated['native_language'],
            'target_language' => $validated['target_language'],
            'proficiency_level' => $validated['proficiency_level'],
            'title' => 'New Conversation',
            'last_message_at' => now(),
        ]);

        return response()->json([
            'id' => $session->id,
            'native_language' => $session->native_language,
            'target_language' => $session->target_language,
            'proficiency_level' => $session->proficiency_level,
            'created_at' => $session->created_at,
        ]);
    }

    public function sendMessage(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $chatSession->messages()->create([
            'sender_type' => 'user',
            'content' => $validated['message'],
        ]);

        // Simulate AI response
        $aiMessage = $chatSession->messages()->create([
            'sender_type' => 'ai',
            'content' => "That's a great topic! Let's practice together.",
        ]);

        $chatSession->update([
            'last_message_at' => now(),
        ]);

        return response()->json([
            'user_message' => [
                'id' => $userMessage->id,
                'content' => $userMessage->content,
                'created_at' => $userMessage->created_at,
            ],
            'ai_response' => [
                'id' => $aiMessage->id,
                'content' => $aiMessage->content,
                'created_at' => $aiMessage->created_at,
            ],
        ]);
    }

    public function history(Request $request)
    {
        $sessions = $request->user()
            ->chatSessions()
            ->latest('last_message_at')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'title' => $session->title ?? 'New Conversation',
                'last_message_at' => $session->last_message_at,
                'native_language' => $session->native_language,
                'target_language' => $session->target_language,
            ]);

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function messages(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $messages = $chatSession->messages()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages' => $messages,
            'session' => [
                'id' => $chatSession->id,
                'native_language' => $chatSession->native_language,
                'target_language' => $chatSession->target_language,
                'proficiency_level' => $chatSession->proficiency_level,
            ],
        ]);
    }
}
