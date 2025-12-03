<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $totalUnread = Conversation::sum('unread_count');

        return view('admin.dashboard', compact('conversations', 'totalUnread'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load('messages');

        return view('admin.conversation', compact('conversation'));
    }

    public function getConversations()
    {
        $conversations = Conversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $totalUnread = Conversation::sum('unread_count');

        return response()->json([
            'conversations' => $conversations,
            'totalUnread' => $totalUnread,
        ]);
    }

    public function getMessages(Conversation $conversation)
    {
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => $messages,
            'conversation' => $conversation,
        ]);
    }
}
