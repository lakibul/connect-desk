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

                // \Log::info('Sending WhatsApp message for user', [
                //     'user_id' => $user->id,
                //     'user_name' => $user->name,
                //     'message_id' => $message->id,
                //     'target_number' => '+8801604509006'
                // ]);

                $whatsappSent = $this->whatsAppService->sendMessage($whatsappMessage);
                dd($whatsappSent);

                if (!$whatsappSent) {
                    \Log::warning('Failed to send WhatsApp message', [
                        'user_id' => $user->id,
                        'message_id' => $message->id,
                        'user_name' => $user->name,
                        'target_number' => '+8801604509006'
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
            $testMessage = "🧪 ConnectDesk WhatsApp Integration Test\n\n" .
                          "This is a test message to verify the WhatsApp Business API integration.\n\n" .
                          "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n" .
                          "Test successful ✅";

            $whatsappSent = $this->whatsAppService->sendMessage($testMessage);

            return response()->json([
                'success' => $whatsappSent,
                'message' => $whatsappSent ?
                    'WhatsApp test message sent successfully to +8801604509006' :
                    'Failed to send WhatsApp test message',
                'target_number' => '+8801604509006',
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
}
