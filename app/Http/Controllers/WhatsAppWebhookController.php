<?php

namespace App\Http\Controllers;

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
            'challenge' => $challenge
        ]);

        // Check if a token and mode were sent
        if ($mode && $token) {
            // Check the mode and token sent are correct
            if ($mode === 'subscribe' && $token === config('services.whatsapp.webhook_secret')) {
                // Respond with 200 OK and challenge token from the request
                Log::info('Webhook verified successfully');
                return response($challenge, 200);
            } else {
                // Respond with '403 Forbidden' if verify tokens do not match
                Log::warning('Webhook verification failed - token mismatch');
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

                            // TODO: Process the message
                            // You can add logic here to:
                            // - Store message in database
                            // - Send auto-reply
                            // - Notify admin
                            // - Create conversation
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
