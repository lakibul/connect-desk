<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    public function index()
    {
        $admin = auth()->user();

        $conversations = Conversation::with('latestMessage')
            ->where('user_id', $admin->id)
            ->orderBy('last_message_at', 'desc')
            ->get();

        $totalUnread = Conversation::where('user_id', $admin->id)
            ->sum('unread_count');

        return view('admin.dashboard', compact('conversations', 'totalUnread'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load('messages');

        return view('admin.conversation', compact('conversation'));
    }

    public function getConversations()
    {
        $admin = auth()->user();

        $conversations = Conversation::with('latestMessage')
            ->where('user_id', $admin->id)
            ->orderBy('last_message_at', 'desc')
            ->get();

        $totalUnread = Conversation::where('user_id', $admin->id)
            ->sum('unread_count');

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

    /**
     * Validate a WhatsApp number before starting conversation
     */
    public function validateWhatsAppNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string'
        ]);

        $admin = auth()->user();
        $phoneNumber = $request->input('phone_number');

        $validation = $this->whatsappService->validateWhatsAppNumber($phoneNumber, $admin);

        return response()->json($validation);
    }

    /**
     * Start a new conversation with a WhatsApp number
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message_type' => 'nullable|string|in:text,template',
            'initial_message' => 'nullable|string',
            'template_name' => 'nullable|string'
        ]);

        $admin = auth()->user();
        $phoneNumber = $request->input('phone_number');
        $messageType = $request->input('message_type', 'text');
        $initialMessage = $request->input('initial_message');
        $templateName = $request->input('template_name');

        // First validate the WhatsApp number
        $validation = $this->whatsappService->validateWhatsAppNumber($phoneNumber, $admin);
        
        if (!$validation['exists']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }

        $formattedNumber = $validation['formatted_number'];

        // Check if conversation already exists
        $conversation = Conversation::where('user_id', $admin->id)
            ->where('visitor_phone', $formattedNumber)
            ->where('platform', 'whatsapp')
            ->first();

        if (!$conversation) {
            // Create new conversation
            $conversation = Conversation::create([
                'user_id' => $admin->id,
                'visitor_phone' => $formattedNumber,
                'platform' => 'whatsapp',
                'status' => 'active',
                'last_message_at' => now(),
                'unread_count' => 0
            ]);
        }

        // Send initial message or template
        $sent = false;
        $messageContent = '';

        if ($messageType === 'template' && !empty($templateName)) {
            // Send template message
            $sent = $this->whatsappService->sendTemplateMessageForUser(
                $admin,
                $templateName,
                [],
                $formattedNumber
            );
            $messageContent = "Template: {$templateName}";
        } elseif ($messageType === 'text' && !empty($initialMessage)) {
            // Send text message
            $sent = $this->whatsappService->sendMessageForUser(
                $admin,
                $initialMessage,
                $formattedNumber
            );
            $messageContent = $initialMessage;
        }

        if ($sent) {
            // Store the message in database
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $admin->id,
                'message' => $messageContent,
                'sender_type' => 'admin',
                'platform' => 'whatsapp',
                'status' => 'sent'
            ]);

            $conversation->update([
                'last_message_at' => now()
            ]);
        } elseif (!empty($messageContent)) {
            // Only return error if we tried to send something
            return response()->json([
                'success' => false,
                'message' => 'Failed to send initial message. Please check WhatsApp credentials.'
            ], 500);
        }

        // Load the conversation with messages
        $conversation->load(['messages', 'latestMessage']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation started successfully',
            'conversation' => $conversation
        ]);
    }
}
