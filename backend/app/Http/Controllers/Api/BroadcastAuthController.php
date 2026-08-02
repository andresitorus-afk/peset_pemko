<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pusher\Pusher;

class BroadcastAuthController extends Controller
{
    public function authorize(Request $request): JsonResponse
    {
        $channel = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        if (! is_string($channel) || ! is_string($socketId) || ! str_starts_with($channel, 'private-chat.')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $allowed = false;

        if ($request->user('sanctum')) {
            $allowed = true;
        } elseif ($token = $request->header('X-Chat-Session')) {
            if (preg_match('/^private-chat\.([0-9a-f\-]{36})$/', $channel, $m)) {
                $chat = ChatSession::find($m[1]);
                $allowed = $chat !== null && hash_equals($chat->token, $token);
            }
        }

        if (! $allowed) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $pusher = new Pusher(
            config('broadcasting.connections.reverb.key'),
            config('broadcasting.connections.reverb.secret'),
            config('broadcasting.connections.reverb.app_id'),
            config('broadcasting.connections.reverb.options'),
        );

        return response()->json(json_decode($pusher->authorizeChannel($channel, $socketId), true));
    }
}
