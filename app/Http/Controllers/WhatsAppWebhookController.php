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
     * Handle incoming Twilio WhatsApp webhook messages
     */
    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Received Twilio WhatsApp webhook', [
                'all_data' => $request->all()
            ]);

            // Twilio sends data as form data, not JSON
            $messageSid = $request->input('MessageSid');
            $from = $request->input('From'); // Format: whatsapp:+8801XXXXXXXXX
            $to = $request->input('To'); // Format: whatsapp:+14155238886
            $body = $request->input('Body');
            $numMedia = (int) $request->input('NumMedia', 0);
            $profileName = $request->input('ProfileName');

            Log::info('Twilio WhatsApp message received', [
                'message_sid' => $messageSid,
                'from' => $from,
                'to' => $to,
                'body' => $body,
                'num_media' => $numMedia,
                'profile_name' => $profileName,
            ]);

            // Extract phone number from whatsapp: prefix
            $senderPhone = str_replace('whatsapp:', '', $from);
            $senderPhone = ltrim($senderPhone, '+'); // Remove + for consistency

            if (empty($senderPhone)) {
                Log::warning('Twilio webhook: sender phone is empty');
                return response('Bad Request', 400);
            }

            // Find or create conversation
            // First try to find existing conversation by phone number (created by admin)
            $conversation = Conversation::where('visitor_phone', $senderPhone)
                ->where('platform', 'whatsapp')
                ->first();

            // If not found, try by visitor_id
            if (!$conversation) {
                $conversation = Conversation::where('visitor_id', 'whatsapp_' . $senderPhone)
                    ->where('platform', 'whatsapp')
                    ->first();
            }

            // If still not found, create new conversation
            if (!$conversation) {
                $conversation = Conversation::create([
                    'visitor_id' => 'whatsapp_' . $senderPhone,
                    'visitor_name' => $profileName ?: $senderPhone,
                    'visitor_phone' => $senderPhone,
                    'platform' => 'whatsapp',
                    'unread_count' => 0,
                ]);
            } else {
                // Update visitor info if not set
                if (empty($conversation->visitor_phone)) {
                    $conversation->visitor_phone = $senderPhone;
                }
                if (empty($conversation->visitor_name) && !empty($profileName)) {
                    $conversation->visitor_name = $profileName;
                }
                if (empty($conversation->visitor_id)) {
                    $conversation->visitor_id = 'whatsapp_' . $senderPhone;
                }
            }

            // Handle message body
            $messageBody = $body;

            // If there's media, log it (you can download and store media later)
            if ($numMedia > 0) {
                $mediaUrls = [];
                for ($i = 0; $i < $numMedia; $i++) {
                    $mediaUrl = $request->input("MediaUrl{$i}");
                    $mediaContentType = $request->input("MediaContentType{$i}");
                    if ($mediaUrl) {
                        $mediaUrls[] = [
                            'url' => $mediaUrl,
                            'type' => $mediaContentType,
                        ];
                    }
                }

                Log::info('Twilio message contains media', [
                    'message_sid' => $messageSid,
                    'media_count' => $numMedia,
                    'media_urls' => $mediaUrls,
                ]);

                // Append media info to message body
                if (empty($messageBody)) {
                    $messageBody = "[Media message received]";
                } else {
                    $messageBody .= "\n[Contains {$numMedia} media file(s)]";
                }
            }

            if (empty($messageBody)) {
                $messageBody = 'Message received';
            }

            // Store the message
            Message::create([
                'conversation_id' => $conversation->id,
                'message' => $messageBody,
                'sender_type' => 'visitor',
                'platform' => 'whatsapp',
                'is_read' => false,
            ]);

            // Update conversation (save changes from above)
            $conversation->last_message_at = now();
            $conversation->unread_count = $conversation->unread_count + 1;
            $conversation->save();

            // Twilio expects an XML response (TwiML)
            return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
                ->header('Content-Type', 'text/xml');

        } catch (\Exception $e) {
            Log::error('Error processing Twilio webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
                ->header('Content-Type', 'text/xml');
        }
    }
}
