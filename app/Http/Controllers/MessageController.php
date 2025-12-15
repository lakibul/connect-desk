<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
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
                    'unread_count' => 0,
                ]
            );

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

                // Send to the target WhatsApp number with user's phone number as sender context
                $whatsappSent = $this->whatsAppService->sendMessage(
                    $whatsappMessage,
                    null,  // null uses default target number from config
                    $user->phone_number  // user's phone as sender context
                );

                if (!$whatsappSent) {
                    \Log::warning('Failed to send WhatsApp message', [
                        'user_id' => $user->id,
                        'message_id' => $message->id,
                        'user_name' => $user->name,
                        'user_phone' => $user->phone_number,
                        'target_number' => config('services.whatsapp.target_phone_number')
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

    /**
     * Test WhatsApp integration
     */
    public function testWhatsApp(Request $request)
    {
        try {
            $targetNumber = config('services.whatsapp.target_phone_number', '8801604509006');
            $testMessage = "🧪 ConnectDesk WhatsApp Integration Test\n\n" .
                          "This is a test message to verify the WhatsApp Business API integration.\n\n" .
                          "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n" .
                          "Test successful ✅";

            $whatsappSent = $this->whatsAppService->sendMessage($testMessage);

            return response()->json([
                'success' => $whatsappSent,
                'message' => $whatsappSent ?
                    "WhatsApp test message sent successfully to {$targetNumber}" :
                    'Failed to send WhatsApp test message',
                'target_number' => $targetNumber,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('WhatsApp test failed', [
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
     * Send WhatsApp text message with preview URL
     */
    /**
     * Send WhatsApp text message
     */
    public function sendTextMessage(Request $request)
    {
        $message = $request->input('body', 'Here is the info you requested! https://www.meta.com/quest/quest-3/');
        $defaultTarget = config('services.whatsapp.target_phone_number', '8801604509006');
        $phone = $request->input('to', $defaultTarget);

        $sent = $this->whatsAppService->sendMessage($message, $phone);

        return response()->json([
            'success' => $sent,
            'to' => $phone,
            'body' => $message
        ]);
    }

    /**
     * Send WhatsApp template message (SMS/hello_world)
     */
    public function sendTemplateMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'to' => 'nullable|string|regex:/^[0-9]{10,15}$/',
                'template_name' => 'nullable|string|in:hello_world',
            ]);

            // Default values
            $defaultTarget = config('services.whatsapp.target_phone_number', '8801604509006');
            $recipient = $validated['to'] ?? $defaultTarget;
            $templateName = $validated['template_name'] ?? 'hello_world';

            // Remove + if present (API expects number without +)
            $recipient = ltrim($recipient, '+');

            \Log::info('Sending WhatsApp template message', [
                'recipient' => $recipient,
                'template' => $templateName,
                'phone_number_id' => config('services.whatsapp.phone_number_id')
            ]);

            $templateSent = $this->whatsAppService->sendTemplateMessage($templateName, [], $recipient);

            return response()->json([
                'success' => $templateSent,
                'message' => $templateSent ?
                    "WhatsApp template '{$templateName}' sent successfully to {$recipient}" :
                    "Failed to send WhatsApp template '{$templateName}' to {$recipient}",
                'template' => $templateName,
                'recipient' => $recipient,
                'phone_number_id' => config('services.whatsapp.phone_number_id'),
                'messaging_product' => 'whatsapp',
                'type' => 'template',
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('WhatsApp template send failed', [
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
     * Debug WhatsApp configuration and connection
     */
    public function whatsappDebug(Request $request)
    {
        $accessToken = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $targetPhone = config('services.whatsapp.target_phone_number');

        $tokenStatus = 'unknown';
        $tokenMessage = '';

        if (empty($accessToken)) {
            $tokenStatus = 'missing';
            $tokenMessage = 'Access token not configured in .env';
        } else {
            // Try to validate token by making a simple API call
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get('https://graph.facebook.com/v18.0/me', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ]
                ]);
                $tokenStatus = 'valid';
                $tokenMessage = 'Access token is valid';
            } catch (\Exception $e) {
                $tokenStatus = 'invalid';
                $tokenMessage = $e->getMessage();
            }
        }

        return response()->json([
            'configuration' => [
                'phone_number_id' => $phoneNumberId ?? 'not configured',
                'target_phone' => $targetPhone ?? 'not configured',
                'access_token_status' => $tokenStatus,
                'access_token_message' => $tokenMessage,
                'token_first_20_chars' => $accessToken ? substr($accessToken, 0, 20) . '...' : 'none',
            ],
            'instructions' => [
                'issue' => 'WhatsApp access token has expired',
                'reason' => 'Meta access tokens are valid for 24 hours by default',
                'solution' => 'Get a new access token from Meta/Facebook Dashboard',
                'steps' => [
                    '1. Go to https://developers.facebook.com',
                    '2. Navigate to Settings > Basic > Access Tokens',
                    '3. Copy the User Access Token or generate a new one',
                    '4. Update .env file: WHATSAPP_ACCESS_TOKEN="your_new_token"',
                    '5. Clear config cache: php artisan config:clear',
                    '6. Test with POST /api/test-whatsapp'
                ]
            ]
        ]);
    }
}
