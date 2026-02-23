<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\TwilioWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    protected $whatsAppService;

    public function __construct(TwilioWhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'platform' => 'required|in:whatsapp,facebook',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the user
            $user = User::findOrFail($request->user_id);

            // Create visitor_id based on user
            $visitorId = 'user_' . $user->id;

            // Find or create conversation
            $conversation = Conversation::firstOrCreate(
                ['visitor_id' => $visitorId, 'platform' => $request->platform],
                [
                    'visitor_name' => $user->name,
                    'visitor_email' => $user->email,
                    'visitor_phone' => $user->phone_number,
                    'unread_count' => 0,
                ]
            );
            if (empty($conversation->visitor_phone) && !empty($user->phone_number)) {
                $conversation->update(['visitor_phone' => $user->phone_number]);
            }

            // Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'sender_type' => 'visitor',
                'platform' => $request->platform,
                'is_read' => false,
            ]);

            // Send WhatsApp message if platform is WhatsApp
            $whatsappSent = false;
            if ($request->platform === 'whatsapp') {
                $whatsappMessage = "🔔 New ConnectDesk Message\n\n" .
                                 "From: {$user->name}\n" .
                                 "Email: {$user->email}\n" .
                                 "Phone: {$user->phone_number}\n\n" .
                                 "Message:\n{$request->message}\n\n" .
                                 "---\nSent via ConnectDesk Platform";

                // Get target phone from config (admin's phone number)
                $targetPhone = config('services.twilio.target_phone_number');

                // Send to the target WhatsApp number via Twilio
                $result = $this->whatsAppService->sendMessage(
                    $whatsappMessage,
                    $targetPhone,
                    $user->phone_number  // user's phone as sender context
                );

                $whatsappSent = $result['success'] ?? false;

                if (!$whatsappSent) {
                    \Log::warning('Failed to send WhatsApp message via Twilio', [
                        'user_id' => $user->id,
                        'message_id' => $message->id,
                        'user_name' => $user->name,
                        'user_phone' => $user->phone_number,
                        'target_number' => $targetPhone,
                        'error' => $result['error'] ?? 'Unknown error',
                    ]);
                }
            }            // Update conversation
            $conversation->update([
                'last_message_at' => now(),
                'unread_count' => $conversation->unread_count + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message->load('user'),
                'whatsapp_sent' => $whatsappSent,
                'conversation_id' => $conversation->id,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error storing message', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendAdminMessage(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'message_type' => 'nullable|in:text,template',
            'message' => 'required_unless:message_type,template|nullable|string',
            'template_name' => 'required_if:message_type,template|nullable|string',
        ]);

        $messageType = $validated['message_type'] ?? 'text';
        $admin = $request->user();
        $whatsappSent = null;

        if ($messageType === 'template' && $conversation->platform !== 'whatsapp') {
            return response()->json([
                'success' => false,
                'message' => 'Template messages are only supported for WhatsApp conversations.'
            ], 422);
        }

        if ($conversation->platform === 'whatsapp') {
            $recipient = $this->resolveConversationPhone($conversation);
            if (empty($recipient)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipient phone number is missing for this conversation.'
                ], 422);
            }

            if ($messageType === 'template') {
                $templateSid = $validated['template_name'];
                $result = $this->whatsAppService->sendTemplateMessageForUser(
                    $admin,
                    $templateSid,
                    [],
                    $recipient
                );
                $whatsappSent = $result['success'] ?? false;
                $messageBody = "Template: {$templateSid}";
            } else {
                $messageBody = $validated['message'];
                $result = $this->whatsAppService->sendMessageForUser(
                    $admin,
                    $messageBody,
                    $recipient
                );
                $whatsappSent = $result['success'] ?? false;
            }

            if (!$whatsappSent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send WhatsApp message via Twilio. ' . ($result['error'] ?? 'Check admin Twilio credentials.')
                ], 502);
            }
        } else {
            $messageBody = $validated['message'];
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $admin ? $admin->id : null,
            'message' => $messageBody,
            'sender_type' => 'admin',
            'platform' => $conversation->platform,
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Broadcast event here (we'll add this later)

        return response()->json([
            'success' => true,
            'message' => $message,
            'whatsapp_sent' => $whatsappSent,
        ]);
    }

    public function sendAdminWhatsApp(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string|regex:/^[0-9\\+\\-\\s\\(\\)]+$/',
            'message_type' => 'nullable|in:text,template',
            'message' => 'required_unless:message_type,template|nullable|string',
            'template_name' => 'required_if:message_type,template|nullable|string',
        ]);

        $admin = $request->user();
        $messageType = $validated['message_type'] ?? 'text';
        $recipient = $this->whatsAppService->normalizePhoneNumber($validated['to']);

        if (empty($recipient)) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient phone number is invalid.'
            ], 422);
        }

        $conversation = Conversation::firstOrCreate(
            ['visitor_id' => 'whatsapp_' . $recipient, 'platform' => 'whatsapp', 'user_id' => $admin->id],
            [
                'visitor_name' => $recipient,
                'visitor_phone' => $recipient,
                'unread_count' => 0,
                'user_id' => $admin->id,
            ]
        );

        if (empty($conversation->visitor_phone)) {
            $conversation->update(['visitor_phone' => $recipient]);
        }

        if ($messageType === 'template') {
            $templateSid = $validated['template_name'];
            $result = $this->whatsAppService->sendTemplateMessageForUser(
                $admin,
                $templateSid,
                [],
                $recipient
            );
            $sent = $result['success'] ?? false;
            $messageBody = "Template: {$templateSid}";
        } else {
            $messageBody = $validated['message'];
            $result = $this->whatsAppService->sendMessageForUser(
                $admin,
                $messageBody,
                $recipient
            );
            $sent = $result['success'] ?? false;
        }

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message via Twilio. ' . ($result['error'] ?? 'Check admin Twilio credentials.')
            ], 502);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $admin ? $admin->id : null,
            'message' => $messageBody,
            'sender_type' => 'admin',
            'platform' => 'whatsapp',
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'conversation_id' => $conversation->id,
            'whatsapp_sent' => true,
        ], 201);
    }

    protected function resolveConversationPhone(Conversation $conversation): ?string
    {
        if (!empty($conversation->visitor_phone)) {
            return $conversation->visitor_phone;
        }

        if (!empty($conversation->visitor_id) && Str::startsWith($conversation->visitor_id, 'user_')) {
            $userId = (int) Str::after($conversation->visitor_id, 'user_');
            if ($userId > 0) {
                $user = User::find($userId);
                if ($user && !empty($user->phone_number)) {
                    $conversation->update(['visitor_phone' => $user->phone_number]);
                    return $user->phone_number;
                }
            }
        }

        return null;
    }

    /**
     * Admin manually sends the FAQ menu to a conversation.
     * Works in Twilio Sandbox (plain-text format).
     */
    public function sendFaqMessage(Request $request, Conversation $conversation)
    {
        if ($conversation->platform !== 'whatsapp') {
            return response()->json([
                'success' => false,
                'message' => 'FAQ messages are only supported for WhatsApp conversations.',
            ], 422);
        }

        $recipient = $this->resolveConversationPhone($conversation);
        if (empty($recipient)) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient phone number is missing for this conversation.',
            ], 422);
        }

        $admin  = $request->user();
        $result = $this->whatsAppService->sendFaqMessage($recipient, null, $admin);

        if (!($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send FAQ message. ' . ($result['error'] ?? 'Check Twilio credentials.'),
            ], 502);
        }

        // Build the menu text for storage (same format the service sends)
        $faqs        = $this->whatsAppService->getDefaultFaqs();
        $menuText    = "📋 *Frequently Asked Questions*\n━━━━━━━━━━━━━━━━━━━━\n\nReply with the *number* of your question:\n\n";
        foreach ($faqs as $key => $item) {
            $menuText .= "*{$key}.* {$item['question']}\n";
        }
        $menuText .= "\n━━━━━━━━━━━━━━━━━━━━\n_Type *FAQ* anytime to see this menu again._";

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $admin ? $admin->id : null,
            'message'         => $menuText,
            'sender_type'     => 'admin',
            'platform'        => 'whatsapp',
            'is_read'         => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success'      => true,
            'message'      => $message,
            'whatsapp_sent'=> true,
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

    /**
     * Test WhatsApp integration with Twilio
     */
    public function testWhatsApp(Request $request)
    {
        try {
            $targetNumber = config('services.twilio.target_phone_number', '+8801604509006');
            $testMessage = "🧪 ConnectDesk Twilio WhatsApp Integration Test\n\n" .
                          "This is a test message to verify the Twilio WhatsApp integration.\n\n" .
                          "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n" .
                          "Test successful ✅";

            $result = $this->whatsAppService->sendMessage($testMessage, $targetNumber);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ?
                    "WhatsApp test message sent successfully to {$targetNumber}" :
                    'Failed to send WhatsApp test message via Twilio',
                'target_number' => $targetNumber,
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Twilio WhatsApp test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'WhatsApp test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send WhatsApp text message via Twilio
     */
    public function sendTextMessage(Request $request)
    {
        $message = $request->input('body', 'Here is the info you requested! https://www.connectdesk.com');
        $defaultTarget = config('services.twilio.target_phone_number', '+8801604509006');
        $phone = $request->input('to', $defaultTarget);

        $result = $this->whatsAppService->sendMessage($message, $phone);

        return response()->json([
            'success' => $result['success'] ?? false,
            'to' => $phone,
            'body' => $message,
            'message_id' => $result['message_id'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * Send WhatsApp template message via Twilio Content API
     */
    public function sendTemplateMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'to' => 'nullable|string',
                'template_sid' => 'nullable|string',
            ]);

            // Default values
            $defaultTarget = config('services.twilio.target_phone_number', '+8801604509006');
            $recipient = $validated['to'] ?? $defaultTarget;
            $templateSid = $validated['template_sid'] ?? '';

            if (empty($templateSid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template SID is required',
                ], 422);
            }

            \Log::info('Sending Twilio WhatsApp template message', [
                'recipient' => $recipient,
                'template_sid' => $templateSid,
            ]);

            $result = $this->whatsAppService->sendTemplateMessage($templateSid, [], $recipient);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ?
                    "WhatsApp template sent successfully to {$recipient}" :
                    "Failed to send WhatsApp template to {$recipient}",
                'template_sid' => $templateSid,
                'recipient' => $recipient,
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Twilio WhatsApp template send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'WhatsApp template send failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug Twilio WhatsApp configuration
     */
    public function whatsappDebug(Request $request)
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $whatsappFrom = config('services.twilio.whatsapp_from');
        $targetPhone = config('services.twilio.target_phone_number');

        $configStatus = 'unknown';
        $configMessage = '';

        if (empty($accountSid)) {
            $configStatus = 'missing';
            $configMessage = 'Twilio Account SID not configured in .env';
        } elseif (empty($authToken)) {
            $configStatus = 'missing';
            $configMessage = 'Twilio Auth Token not configured in .env';
        } elseif (empty($whatsappFrom)) {
            $configStatus = 'missing';
            $configMessage = 'Twilio WhatsApp from number not configured in .env';
        } else {
            $configStatus = 'configured';
            $configMessage = 'All Twilio credentials are configured';
        }

        return response()->json([
            'service' => 'Twilio WhatsApp',
            'configuration' => [
                'account_sid' => $accountSid ? substr($accountSid, 0, 10) . '...' : 'not configured',
                'auth_token' => $authToken ? substr($authToken, 0, 10) . '...' : 'not configured',
                'whatsapp_from' => $whatsappFrom ?? 'not configured',
                'target_phone' => $targetPhone ?? 'not configured',
                'config_status' => $configStatus,
                'config_message' => $configMessage,
            ],
            'instructions' => [
                'setup' => 'Twilio WhatsApp Sandbox Setup',
                'steps' => [
                    '1. Go to https://console.twilio.com',
                    '2. Navigate to Messaging > Try it out > Send a WhatsApp message',
                    '3. Follow the instructions to join your sandbox',
                    '4. Get your Account SID and Auth Token from Console Dashboard',
                    '5. Get your WhatsApp sandbox number (format: +14155238886)',
                    '6. Update .env file with Twilio credentials',
                    '7. Clear config cache: php artisan config:clear',
                    '8. Test with POST /api/test-whatsapp'
                ],
                'env_variables' => [
                    'TWILIO_ACCOUNT_SID' => 'Your Account SID from Twilio Console',
                    'TWILIO_AUTH_TOKEN' => 'Your Auth Token from Twilio Console',
                    'TWILIO_WHATSAPP_FROM' => 'Your WhatsApp sandbox number (e.g., +14155238886)',
                    'TWILIO_TARGET_PHONE' => 'Admin phone number to receive messages (e.g., +8801XXXXXXXXX)',
                ]
            ]
        ]);
    }
}
