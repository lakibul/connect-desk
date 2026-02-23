<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\TwilioWhatsAppService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $whatsappService;

    public function __construct(TwilioWhatsAppService $whatsappService)
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
            'message_type' => 'nullable|string|in:text,template,faq',
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

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }

        $formattedNumber = $validation['formatted_number'];

        // Check if conversation already exists for this admin and phone number
        $conversation = Conversation::where(function($query) use ($admin, $formattedNumber) {
                $query->where('user_id', $admin->id)
                      ->where('visitor_phone', $formattedNumber);
            })
            ->orWhere(function($query) use ($formattedNumber) {
                // Also check by visitor_id format used by webhook
                $query->where('visitor_id', 'whatsapp_' . $formattedNumber)
                      ->whereNull('user_id'); // Webhook conversations don't have user_id initially
            })
            ->where('platform', 'whatsapp')
            ->first();

        $conversationExisted = (bool) $conversation;

        if (!$conversation) {
            // Create new conversation
            $conversation = Conversation::create([
                'user_id' => $admin->id,
                'visitor_id' => 'whatsapp_' . $formattedNumber,
                'visitor_phone' => $formattedNumber,
                'platform' => 'whatsapp',
                'status' => 'active',
                'last_message_at' => now(),
                'unread_count' => 0
            ]);
        } else {
            // Update existing conversation to link it to this admin if not already
            if (!$conversation->user_id) {
                $conversation->update(['user_id' => $admin->id]);
            }
            // Reset unread count when admin initiates conversation
            $conversation->update(['unread_count' => 0]);
        }

        // Send initial message or template
        $sent = false;
        $messageContent = '';
        $result = [];

        if ($messageType === 'template' && !empty($templateName)) {
            // Send template message (templateName is a predefined name or a Twilio Content SID)
            $result = $this->whatsappService->sendTemplateMessageForUser(
                $admin,
                $templateName,
                [],
                $formattedNumber
            );
            $sent = $result['success'] ?? false;
            // Store the actual message body so the frontend widget shows real content.
            // For Twilio Content SIDs (HX...) we only have the SID; for predefined names we have the text.
            $predefinedBody = $this->whatsappService->getPredefinedTemplateMessage($templateName);
            $messageContent = $predefinedBody ?? "[Template: {$templateName}]";
        } elseif ($messageType === 'text' && !empty($initialMessage)) {
            // Send text message
            $result = $this->whatsappService->sendMessageForUser(
                $admin,
                $initialMessage,
                $formattedNumber
            );
            $sent = $result['success'] ?? false;
            $messageContent = $initialMessage;
        } elseif ($messageType === 'faq') {
            // Send FAQ menu (plain-text, works in Twilio Sandbox)
            $result = $this->whatsappService->sendFaqMessage($formattedNumber, null, $admin);
            $sent = $result['success'] ?? false;
            $faqs = $this->whatsappService->getDefaultFaqs();
            $messageContent  = "📋 *Frequently Asked Questions*\n━━━━━━━━━━━━━━━━━━━━\n\nReply with the *number* of your question:\n\n";
            foreach ($faqs as $key => $item) {
                $messageContent .= "*{$key}.* {$item['question']}\n";
            }
            $messageContent .= "\n━━━━━━━━━━━━━━━━━━━━\n_Type *FAQ* anytime to see this menu again._";
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
                'message' => 'Failed to send initial message via Twilio. ' . ($result['error'] ?? 'Please check Twilio credentials.')
            ], 500);
        }

        // Load the conversation with messages
        $conversation->load(['messages', 'latestMessage']);

        $responseMessage = $conversationExisted
            ? 'Using existing conversation. Message sent successfully.'
            : 'New conversation started successfully.';

        return response()->json([
            'success' => true,
            'message' => $responseMessage,
            'conversation' => $conversation,
            'conversation_existed' => $conversationExisted
        ]);
    }
}
