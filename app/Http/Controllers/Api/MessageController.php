<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        if (!$chat->isParticipant($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $chat->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        if (!$chat->isParticipant($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $message = $chat->messages()->create([
            'sender_id' => $request->user()->id,
            'content' => $validated['content'],
            'is_read' => false,
        ]);

        Log::info('Message created', ['message_id' => $message->id, 'chat_id' => $chat->id]);

        $message->load('sender');

        try {
            broadcast(new MessageSent($message))->toOthers();
            Log::info('MessageSent event broadcasted', ['channel' => 'chat.' . $chat->id]);
        } catch (\Exception $e) {
            Log::error('Broadcast failed', ['error' => $e->getMessage()]);
        }

        return response()->json($message, 201);
    }

    public function markAsRead(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        if (!$chat->isParticipant($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        Message::whereIn('id', $validated['message_ids'])
            ->where('chat_id', $chat->id)
            ->where('sender_id', '!=', $request->user()->id)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }
}
