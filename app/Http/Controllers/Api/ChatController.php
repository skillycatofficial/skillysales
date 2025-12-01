<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;


class ChatController extends Controller
{
    /**
     * Display a listing of the user's chats
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $chats = Chat::where('customer_id', $user->id)
            ->orWhere('dealer_id', $user->id)
            ->with(['customer', 'dealer', 'car', 'lastMessage.sender'])
            ->get()
            ->map(function ($chat) use ($user) {
                return [
                    'id' => $chat->id,
                    'car' => $chat->car,
                    'other_participant' => $chat->getOtherParticipant($user->id),
                    'last_message' => $chat->lastMessage,
                    'unread_count' => $chat->unreadCount($user->id),
                    'created_at' => $chat->created_at,
                    'updated_at' => $chat->updated_at,
                ];
            });

        return response()->json($chats);
    }

    /**
     * Store a newly created chat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dealer_id' => 'required|exists:users,id',
            'car_id' => 'nullable|exists:cars,id',
        ]);

        $user = $request->user();

        // Check if chat already exists
        $existingChat = Chat::where(function ($query) use ($user, $validated) {
            $query->where('customer_id', $user->id)
                ->where('dealer_id', $validated['dealer_id']);
        })->orWhere(function ($query) use ($user, $validated) {
            $query->where('customer_id', $validated['dealer_id'])
                ->where('dealer_id', $user->id);
        });

        if (isset($validated['car_id'])) {
            $existingChat->where('car_id', $validated['car_id']);
        }

        $chat = $existingChat->first();

        if ($chat) {
            return response()->json([
                'message' => 'Chat already exists',
                'chat' => $chat->load(['customer', 'dealer', 'car']),
            ]);
        }

        // Create new chat
        $chat = Chat::create([
            'customer_id' => $user->id,
            'dealer_id' => $validated['dealer_id'],
            'car_id' => $validated['car_id'] ?? null,
        ]);

        return response()->json($chat->load(['customer', 'dealer', 'car']), 201);
    }

    /**
     * Display the specified chat with messages
     */
    public function show(Request $request, $id)
    {
        $chat = Chat::with(['customer', 'dealer', 'car'])->findOrFail($id);

        // Check if user is a participant
        if (!$chat->isParticipant($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get messages with pagination
        $messages = $chat->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json([
            'chat' => $chat,
            'messages' => $messages,
        ]);
    }

    /**
     * Mark messages as read
     */
    public function update(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);

        // Check if user is a participant
        if (!$chat->isParticipant($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark all messages from the other participant as read
        $chat->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    /**
     * Remove the specified chat
     */
    public function destroy(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);

        // Check if user is a participant
        if (!$chat->isParticipant($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->delete();

        return response()->json(['message' => 'Chat deleted successfully']);
    }
}

