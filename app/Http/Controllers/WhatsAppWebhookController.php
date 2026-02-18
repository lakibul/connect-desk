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
     * Handle outbound message status callbacks from Twilio
     * (sent, delivered, read, failed - these come from the Status Callback URL)
     */
    public function handleStatus(Request $request)
    {
        $messageSid = $request->input('MessageSid');
        $status = $request->input('MessageStatus');
        $to = $request->input('To');

        Log::info('Twilio message status update', [
            'message_sid' => $messageSid,
            'status' => $status,
            'to' => $to,
        ]);

        // Respond with empty TwiML
        return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Handle incoming Twilio WhatsApp webhook messages
     * This is triggered when a USER sends a message TO your Twilio number.
     * From = user's WhatsApp number, To = your Twilio sandbox number.
     */
    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Received Twilio WhatsApp webhook', [
                'all_data' => $request->all()
            ]);

            // Twilio sends data as form data, not JSON
            $messageSid = $request->input('MessageSid');
            $from = $request->input('From'); // Format: whatsapp:+8801XXXXXXXXX (user's number)
            $to = $request->input('To');     // Format: whatsapp:+14155238886 (Twilio sandbox)
            $body = $request->input('Body');
            $numMedia = (int) $request->input('NumMedia', 0);
            $profileName = $request->input('ProfileName');
            $messageStatus = $request->input('MessageStatus');

            // ---------------------------------------------------------------
            // IMPORTANT: Skip outbound status callbacks (sent/delivered/read).
            // Status callbacks have MessageStatus set and come FROM your Twilio
            // number. Real incoming messages come FROM the user's number.
            // ---------------------------------------------------------------
            if (!empty($messageStatus)) {
                Log::info('Twilio status callback received (not an incoming message), skipping.', [
                    'message_sid' => $messageSid,
                    'status' => $messageStatus,
                ]);
                return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
                    ->header('Content-Type', 'text/xml');
            }

            Log::info('Twilio incoming WhatsApp message received', [
                'message_sid' => $messageSid,
                'from' => $from,
                'to' => $to,
                'body' => $body,
                'num_media' => $numMedia,
                'profile_name' => $profileName,
            ]);

            // Extract phone number - keep the + prefix for E.164 consistency
            // with how admin stores numbers (sanitizePhoneNumber returns +XXXX format)
            $senderPhone = str_replace('whatsapp:', '', $from); // e.g. +8801983427887
            $senderPhoneWithPlus = $senderPhone; // keep with + prefix
            $senderPhoneNoPlus = ltrim($senderPhone, '+'); // without + prefix

            if (empty($senderPhoneNoPlus)) {
                Log::warning('Twilio webhook: sender phone is empty');
                return response('Bad Request', 400);
            }

            // Find existing conversation - check BOTH phone formats (+XXXX and XXXX)
            // Admin-created conversations store visitor_phone as +8801XXXXXXXXX (with +)
            // Webhook-created conversations may have stored without +
            $conversation = Conversation::where('platform', 'whatsapp')
                ->where(function ($query) use ($senderPhoneWithPlus, $senderPhoneNoPlus) {
                    $query->where('visitor_phone', $senderPhoneWithPlus)
                          ->orWhere('visitor_phone', $senderPhoneNoPlus)
                          ->orWhere('visitor_id', 'whatsapp_' . $senderPhoneWithPlus)
                          ->orWhere('visitor_id', 'whatsapp_' . $senderPhoneNoPlus);
                })
                ->first();

            if (!$conversation) {
                // No existing conversation — create a new one
                $conversation = Conversation::create([
                    'visitor_id'   => 'whatsapp_' . $senderPhoneNoPlus,
                    'visitor_name' => $profileName ?: $senderPhoneNoPlus,
                    'visitor_phone' => $senderPhoneWithPlus, // store with + prefix (E.164)
                    'platform'     => 'whatsapp',
                    'unread_count' => 0,
                ]);
                Log::info('Twilio webhook: created new conversation', ['conversation_id' => $conversation->id]);
            } else {
                // Update visitor info if missing
                $updates = [];
                if (empty($conversation->visitor_phone)) {
                    $updates['visitor_phone'] = $senderPhoneWithPlus;
                }
                if (empty($conversation->visitor_name) && !empty($profileName)) {
                    $updates['visitor_name'] = $profileName;
                }
                if (empty($conversation->visitor_id)) {
                    $updates['visitor_id'] = 'whatsapp_' . $senderPhoneNoPlus;
                }
                if (!empty($updates)) {
                    $conversation->update($updates);
                }
                Log::info('Twilio webhook: matched existing conversation', ['conversation_id' => $conversation->id]);
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
