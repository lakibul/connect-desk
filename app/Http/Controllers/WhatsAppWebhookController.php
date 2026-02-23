<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\TwilioWhatsAppService;
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

    // buildFaqMenuText has been moved to TwilioWhatsAppService::buildFaqMenuText()

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
            $messageSid    = $request->input('MessageSid');
            $from          = $request->input('From');          // whatsapp:+8801XXXXXXXXX (user)
            $to            = $request->input('To');            // whatsapp:+14155238886 (sandbox)
            $body          = $request->input('Body');
            $numMedia      = (int) $request->input('NumMedia', 0);
            $profileName   = $request->input('ProfileName');
            $messageStatus = $request->input('MessageStatus');

            // Twilio interactive-message button fields
            // ButtonPayload = the payload value configured on the button (e.g. "faq_business_hours")
            // ButtonText    = the label shown on the button (e.g. "Business Hours")
            // ListItemTitle = selected item from a list message
            $buttonPayload  = $request->input('ButtonPayload');
            $buttonText     = $request->input('ButtonText');
            $listItemTitle  = $request->input('ListItemTitle');

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

            // Update conversation
            $conversation->last_message_at = now();
            $conversation->unread_count = $conversation->unread_count + 1;
            $conversation->save();

            // ---------------------------------------------------------------
            // FAQ Auto-Reply
            // Priority order for detecting what the user means:
            //   1. ButtonPayload  – exact payload from an interactive button tap
            //   2. ButtonText     – display text of the tapped button
            //   3. ListItemTitle  – selected item from a list message
            //   4. Body text      – plain-text number ("1"–"5") or trigger keyword
            // ---------------------------------------------------------------

            // Build a Twilio service scoped to the admin that owns this conversation
            $adminUser = $conversation->user_id ? \App\Models\User::find($conversation->user_id) : null;
            $twilioService = new TwilioWhatsAppService();
            $autoReplyText = null;

            // Determine the effective "reply key" from the highest-priority source
            $replyKey = $buttonPayload ?? $buttonText ?? $listItemTitle ?? $messageBody;

            if (!empty($buttonPayload) || !empty($buttonText) || !empty($listItemTitle)) {
                // ── Interactive button / list reply ──────────────────────────
                $answer = $twilioService->getFaqAnswer($replyKey);
                if ($answer) {
                    $result = $adminUser
                        ? $twilioService->sendMessageForUser($adminUser, $answer, $senderPhoneWithPlus)
                        : $twilioService->sendMessage($answer, $senderPhoneWithPlus);
                    $autoReplyText = $answer;
                    Log::info('FAQ button answer auto-sent', [
                        'payload' => $buttonPayload,
                        'text'    => $buttonText,
                        'to'      => $senderPhoneWithPlus,
                    ]);
                }
            } elseif ($twilioService->isFaqTrigger($messageBody)) {
                // ── Trigger keyword (faq / help / menu / hi / hello) ─────────
                $result = $adminUser
                    ? $twilioService->sendFaqMessage($senderPhoneWithPlus, null, $adminUser)
                    : $twilioService->sendFaqMessage($senderPhoneWithPlus);
                $autoReplyText = $twilioService->buildFaqMenuText($twilioService->getDefaultFaqs());
                Log::info('FAQ menu auto-sent', ['to' => $senderPhoneWithPlus, 'result' => $result]);

            } elseif ($twilioService->isFaqOption($messageBody)) {
                // ── Plain-text number reply ("1" … "5") ─────────────────────
                $answer = $twilioService->getFaqAnswer($messageBody);
                if ($answer) {
                    $result = $adminUser
                        ? $twilioService->sendMessageForUser($adminUser, $answer, $senderPhoneWithPlus)
                        : $twilioService->sendMessage($answer, $senderPhoneWithPlus);
                    $autoReplyText = $answer;
                    Log::info('FAQ answer auto-sent (plain text)', [
                        'choice' => trim($messageBody),
                        'to'     => $senderPhoneWithPlus,
                    ]);
                }
            }

            // Store the auto-reply in the database so the admin sees it in the chat
            if ($autoReplyText) {
                Message::create([
                    'conversation_id' => $conversation->id,
                    'message'         => $autoReplyText,
                    'sender_type'     => 'admin',
                    'platform'        => 'whatsapp',
                    'is_read'         => true,
                ]);

                $conversation->last_message_at = now();
                $conversation->save();
            }

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
