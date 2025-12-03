<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'platform' => 'required|in:whatsapp,facebook',
            'visitor_id' => 'nullable|string',
            'visitor_name' => 'nullable|string',
            'visitor_email' => 'nullable|email',
        ]);

        // Get or create visitor_id
        $visitorId = $request->visitor_id ?? 'visitor_' . Str::random(32);

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            ['visitor_id' => $visitorId, 'platform' => $request->platform],
            [
                'visitor_name' => $request->visitor_name,
                'visitor_email' => $request->visitor_email,
                'unread_count' => 0,
            ]
        );

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'message' => $request->message,
            'sender_type' => 'visitor',
            'is_read' => false,
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
        ]);

        // Broadcast event here (we'll add this later)

        return response()->json([
            'success' => true,
            'message' => $message,
            'visitor_id' => $visitorId,
        ]);
    }

    public function sendAdminMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'message' => $request->message,
            'sender_type' => 'admin',
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Broadcast event here (we'll add this later)

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function markAsRead(Conversation $conversation)
    {
        $conversation->messages()
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->update(['unread_count' => 0]);

        return response()->json(['success' => true]);
    }
}
