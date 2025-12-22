<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify webhook endpoint for Meta
     */
    public function verify(Request $request)
    {
        // Meta sends these parameters for verification
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('Webhook verification attempt', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge,
            'user_agent' => $request->header('User-Agent'),
            'all_headers' => $request->headers->all()
        ]);

        // Check if a token and mode were sent
        if ($mode && $token) {
            // Check the mode and token sent are correct
            if ($mode === 'subscribe' && $token === config('services.whatsapp.webhook_secret')) {
                // Respond with 200 OK and challenge token from the request
                Log::info('Webhook verified successfully');
                return response($challenge, 200)
                    ->header('Content-Type', 'text/plain');
            } else {
                // Respond with '403 Forbidden' if verify tokens do not match
                Log::warning('Webhook verification failed - token mismatch', [
                    'expected' => config('services.whatsapp.webhook_secret'),
                    'received' => $token
                ]);
                return response('Forbidden', 403);
            }
        }

        Log::warning('Webhook verification failed - missing parameters');
        return response('Bad Request', 400);
    }

    /**
     * Handle incoming webhook messages
     */
    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Received WhatsApp webhook', [
                'body' => $request->all()
            ]);

            // Get the webhook body
            $body = $request->all();

            // Check if it's a WhatsApp webhook event
            if (isset($body['object']) && $body['object'] === 'whatsapp_business_account') {

                // Loop through entries (there might be multiple)
                foreach ($body['entry'] ?? [] as $entry) {
                    foreach ($entry['changes'] ?? [] as $change) {

                        // Get the message data
                        $value = $change['value'] ?? [];
                        $messages = $value['messages'] ?? [];
                        $contacts = $value['contacts'] ?? [];
                        $contactName = $contacts[0]['profile']['name'] ?? null;

                        foreach ($messages as $message) {
                            $from = $message['from'] ?? '';
                            $messageId = $message['id'] ?? '';
                            $timestamp = $message['timestamp'] ?? '';
                            $text = $message['text']['body'] ?? '';
                            $type = $message['type'] ?? '';

                            Log::info('WhatsApp message received', [
                                'from' => $from,
                                'message_id' => $messageId,
                                'type' => $type,
                                'text' => $text,
                                'timestamp' => $timestamp
                            ]);

                            if (empty($from)) {
                                continue;
                            }

                            $conversation = Conversation::firstOrCreate(
                                ['visitor_id' => 'whatsapp_' . $from, 'platform' => 'whatsapp'],
                                [
                                    'visitor_name' => $contactName ?: $from,
                                    'visitor_phone' => $from,
                                    'unread_count' => 0,
                                ]
                            );

                            $messageBody = $text;
                            if (empty($messageBody)) {
                                $messageBody = $type ? strtoupper($type) . ' message received' : 'Message received';
                            }

                            Message::create([
                                'conversation_id' => $conversation->id,
                                'message' => $messageBody,
                                'sender_type' => 'visitor',
                                'platform' => 'whatsapp',
                                'is_read' => false,
                            ]);

                            $conversation->update([
                                'visitor_phone' => $conversation->visitor_phone ?: $from,
                                'visitor_name' => $conversation->visitor_name ?: ($contactName ?: $from),
                                'last_message_at' => now(),
                                'unread_count' => $conversation->unread_count + 1,
                            ]);
                        }
                    }
                }

                return response()->json(['status' => 'ok'], 200);
            }

            Log::warning('Unknown webhook event type', [
                'object' => $body['object'] ?? 'unknown'
            ]);

            return response()->json(['status' => 'ignored'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
