<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(): JsonResponse
    {
        $this->autoCloseStaleSessions();

        $sessions = ChatSession::with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByRaw("(status = 'open') DESC")
            ->orderByDesc('updated_at')
            ->get();

        $data = $sessions->map(function (ChatSession $session) {
            $last = $session->messages->first();

            return [
                'id' => $session->id,
                'visitor_name' => $session->visitor_name,
                'status' => $session->status,
                'needs_attention' => $session->needs_attention,
                'unread' => $this->unreadFor($session),
                'last_message' => $last?->message,
                'last_message_at' => $last?->created_at?->toIso8601String(),
                'updated_at' => $session->updated_at?->toIso8601String(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(string $session): JsonResponse
    {
        $this->autoCloseStaleSessions();

        $chat = ChatSession::findOrFail($session);
        $messages = ChatMessage::where('session_id', $chat->id)->orderBy('created_at')->get();

        return response()->json(['data' => $messages]);
    }

    public function storeMessage(Request $request, string $session): JsonResponse
    {
        $chat = ChatSession::findOrFail($session);
        $validated = $request->validate(['message' => 'required|string|max:1000']);

        $msg = ChatMessage::create([
            'session_id' => $chat->id,
            'sender_type' => 'admin',
            'user_id' => $request->user()?->id,
            'message' => $validated['message'],
        ]);
        $chat->update(['needs_attention' => false, 'last_admin_seen_at' => now()]);

        broadcast(new ChatMessageSent($msg));

        return response()->json(['data' => $msg], 201);
    }

    public function markRead(string $session): JsonResponse
    {
        $chat = ChatSession::findOrFail($session);
        $chat->update(['last_admin_seen_at' => now()]);

        return response()->json(['message' => 'OK']);
    }

    public function close(string $session): JsonResponse
    {
        $chat = ChatSession::findOrFail($session);
        $chat->update(['status' => 'closed', 'closed_at' => now()]);

        return response()->json(['message' => 'Sesi ditutup.']);
    }

    public function unreadCount(): JsonResponse
    {
        $this->autoCloseStaleSessions();

        $count = ChatSession::where('status', 'open')->get()
            ->sum(fn (ChatSession $session) => $this->unreadFor($session));

        return response()->json(['unread' => $count]);
    }

    private function autoCloseStaleSessions(): void
    {
        $cutoff = now()->subMinutes(config('services.chat.auto_close_minutes', 10));

        ChatSession::where('status', 'open')
            ->where('created_at', '<', $cutoff)
            ->whereDoesntHave('messages', fn ($q) => $q
                ->where('sender_type', 'visitor')
                ->where('created_at', '>', $cutoff))
            ->update(['status' => 'closed', 'closed_at' => now()]);
    }

    private function unreadFor(ChatSession $session): int
    {
        $query = ChatMessage::where('session_id', $session->id)
            ->whereIn('sender_type', ['visitor', 'admin']);

        if ($session->last_admin_seen_at) {
            $query->where('created_at', '>', $session->last_admin_seen_at);
        }

        return $query->count();
    }
}
