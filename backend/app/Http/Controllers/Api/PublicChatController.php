<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatMessageSent;
use App\Events\ChatUnreadUpdated;
use App\Http\Controllers\Controller;
use App\Models\ChatbotFaq;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicChatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $session = ChatSession::create([
            'token' => Str::random(40),
            'visitor_name' => $request->get('visitor_name') ?: 'Pengunjung',
            'status' => 'open',
        ]);

        $greeting = ChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'message' => config('services.chatbot.greeting'),
        ]);

        return response()->json([
            'data' => [
                'id' => $session->id,
                'token' => $session->token,
                'messages' => [$this->shape($greeting)],
            ],
        ], 201);
    }

    public function index(Request $request, string $session): JsonResponse
    {
        $chat = $this->authorizeSession($request, $session);
        $messages = ChatMessage::where('session_id', $chat->id)->orderBy('created_at')->get();

        return response()->json(['data' => $messages->map(fn ($m) => $this->shape($m))]);
    }

    public function storeMessage(Request $request, string $session): JsonResponse
    {
        $chat = $this->authorizeSession($request, $session);

        if ($chat->status !== 'open') {
            return response()->json(['message' => 'Sesi chat telah ditutup.'], 403);
        }

        $validated = $request->validate(['message' => 'required|string|max:1000']);

        $visitor = ChatMessage::create([
            'session_id' => $chat->id,
            'sender_type' => 'visitor',
            'message' => $validated['message'],
        ]);
        broadcast(new ChatMessageSent($visitor));

        $faqs = ChatbotFaq::aktif()->get()->toArray();
        $matched = ChatbotService::match($validated['message'], $faqs);
        $replyText = $matched ? $matched['answer'] : config('services.chatbot.fallback_reply');

        $bot = ChatMessage::create([
            'session_id' => $chat->id,
            'sender_type' => 'bot',
            'message' => $replyText,
        ]);
        broadcast(new ChatMessageSent($bot));

        if (! $matched) {
            $chat->update(['needs_attention' => true]);
            broadcast(new ChatUnreadUpdated($chat->id));
        }

        return response()->json([
            'data' => ['visitor' => $this->shape($visitor), 'bot' => $this->shape($bot)],
        ]);
    }

    private function authorizeSession(Request $request, string $id): ChatSession
    {
        $token = $request->header('X-Chat-Session');
        abort_unless(is_string($token) && strlen($token) === 40, 403);

        $chat = ChatSession::findOrFail($id);
        abort_unless(hash_equals($chat->token, $token), 403);

        return $chat;
    }

    private function shape(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'user_id' => $message->user_id,
            'message' => $message->message,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
