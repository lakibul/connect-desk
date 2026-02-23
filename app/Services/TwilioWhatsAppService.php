<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class TwilioWhatsAppService
{
    protected $client;
    protected $accountSid;
    protected $authToken;
    protected $fromNumber;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.whatsapp_from');

        if ($this->accountSid && $this->authToken) {
            $this->client = new Client($this->accountSid, $this->authToken);
        }
    }

    /**
     * Send a WhatsApp message via Twilio
     *
     * @param string $message The message content
     * @param string $recipientPhone The recipient phone number
     * @param string|null $senderPhone The sender's phone number (for context)
     * @return array ['success' => bool, 'message_id' => string|null, 'error' => string|null]
     */
    public function sendMessage(string $message, string $recipientPhone, string $senderPhone = null): array
    {
        return $this->sendMessageWithCredentials(
            $message,
            $recipientPhone,
            $senderPhone,
            $this->accountSid,
            $this->authToken,
            $this->fromNumber
        );
    }

    /**
     * Send WhatsApp message for a specific user (admin)
     *
     * @param User $user The admin user
     * @param string $message The message content
     * @param string $recipientPhone The recipient phone number
     * @return array
     */
    public function sendMessageForUser(User $user, string $message, string $recipientPhone): array
    {
        // Check if user has custom Twilio credentials
        $accountSid = $user->twilio_account_sid ?? $this->accountSid;
        $authToken = $user->twilio_auth_token ?? $this->authToken;
        $fromNumber = $user->twilio_whatsapp_from ?? $this->fromNumber;

        return $this->sendMessageWithCredentials(
            $message,
            $recipientPhone,
            $user->phone_number,
            $accountSid,
            $authToken,
            $fromNumber
        );
    }

    /**
     * Send WhatsApp message with specific credentials
     *
     * @param string $message Message content
     * @param string $recipientPhone Recipient phone
     * @param string|null $senderPhone Sender context
     * @param string|null $accountSid Twilio Account SID
     * @param string|null $authToken Twilio Auth Token
     * @param string|null $fromNumber Twilio WhatsApp from number
     * @return array
     */
    protected function sendMessageWithCredentials(
        string $message,
        string $recipientPhone,
        ?string $senderPhone,
        ?string $accountSid,
        ?string $authToken,
        ?string $fromNumber
    ): array {
        try {
            // Validate credentials
            if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
                Log::error('Twilio credentials not configured', [
                    'has_sid' => !empty($accountSid),
                    'has_token' => !empty($authToken),
                    'has_from' => !empty($fromNumber),
                ]);
                return [
                    'success' => false,
                    'message_id' => null,
                    'error' => 'Twilio credentials not configured',
                ];
            }

            // Sanitize recipient phone number
            $recipient = $this->sanitizePhoneNumber($recipientPhone);
            if (empty($recipient)) {
                Log::error('Invalid recipient phone number', ['phone' => $recipientPhone]);
                return [
                    'success' => false,
                    'message_id' => null,
                    'error' => 'Invalid recipient phone number',
                ];
            }

            // Create Twilio client with provided credentials
            $twilioClient = new Client($accountSid, $authToken);

            // Format phone numbers for WhatsApp (must include 'whatsapp:' prefix)
            $toWhatsApp = 'whatsapp:' . $recipient;
            $fromWhatsApp = 'whatsapp:' . $fromNumber;

            Log::info('Sending Twilio WhatsApp message', [
                'to' => $toWhatsApp,
                'from' => $fromWhatsApp,
                'message_length' => strlen($message),
            ]);

            // Send message via Twilio
            $twilioMessage = $twilioClient->messages->create(
                $toWhatsApp,
                [
                    'from' => $fromWhatsApp,
                    'body' => $message,
                ]
            );

            Log::info('Twilio WhatsApp message sent successfully', [
                'message_sid' => $twilioMessage->sid,
                'to' => $recipient,
                'status' => $twilioMessage->status,
            ]);

            return [
                'success' => true,
                'message_id' => $twilioMessage->sid,
                'error' => null,
                'status' => $twilioMessage->status,
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];

        } catch (\Exception $e) {
            Log::error('Unexpected error sending Twilio WhatsApp message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a WhatsApp template message via Twilio (Content API)
     *
     * @param string $templateSid Twilio Content Template SID
     * @param array $variables Template variables
     * @param string $recipientPhone Recipient phone number
     * @return array
     */
    public function sendTemplateMessage(string $templateSid, array $variables, string $recipientPhone): array
    {
        return $this->sendTemplateMessageWithCredentials(
            $templateSid,
            $variables,
            $recipientPhone,
            $this->accountSid,
            $this->authToken,
            $this->fromNumber
        );
    }

    /**
     * Send template for a specific user
     *
     * @param User $user
     * @param string $templateSid
     * @param array $variables
     * @param string $recipientPhone
     * @return array
     */
    public function sendTemplateMessageForUser(User $user, string $templateSid, array $variables, string $recipientPhone): array
    {
        $accountSid = $user->twilio_account_sid ?? $this->accountSid;
        $authToken = $user->twilio_auth_token ?? $this->authToken;
        $fromNumber = $user->twilio_whatsapp_from ?? $this->fromNumber;

        return $this->sendTemplateMessageWithCredentials(
            $templateSid,
            $variables,
            $recipientPhone,
            $accountSid,
            $authToken,
            $fromNumber
        );
    }

    /**
     * Send template with specific credentials
     *
     * @param string $templateSid
     * @param array $variables
     * @param string $recipientPhone
     * @param string|null $accountSid
     * @param string|null $authToken
     * @param string|null $fromNumber
     * @return array
     */
    protected function sendTemplateMessageWithCredentials(
        string $templateSid,
        array $variables,
        string $recipientPhone,
        ?string $accountSid,
        ?string $authToken,
        ?string $fromNumber
    ): array {
        try {
            if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
                return [
                    'success' => false,
                    'message_id' => null,
                    'error' => 'Twilio credentials not configured',
                ];
            }

            $recipient = $this->sanitizePhoneNumber($recipientPhone);
            if (empty($recipient)) {
                return [
                    'success' => false,
                    'message_id' => null,
                    'error' => 'Invalid recipient phone number',
                ];
            }

            $twilioClient = new Client($accountSid, $authToken);

            $toWhatsApp = 'whatsapp:' . $recipient;
            $fromWhatsApp = 'whatsapp:' . $fromNumber;

            // Check if it's a Twilio Content SID (starts with HX) or a predefined template name
            if (preg_match('/^HX[a-f0-9]+$/i', $templateSid)) {
                // Use Twilio Content API for approved Content Templates
                $twilioMessage = $twilioClient->messages->create(
                    $toWhatsApp,
                    [
                        'from' => $fromWhatsApp,
                        'contentSid' => $templateSid,
                        'contentVariables' => json_encode($variables),
                    ]
                );

                Log::info('Twilio Content template sent successfully', [
                    'message_sid' => $twilioMessage->sid,
                    'content_sid' => $templateSid,
                    'to' => $recipient,
                ]);
            } else {
                // Send as formatted text message using predefined templates
                $templateMessage = $this->getPredefinedTemplateMessage($templateSid);

                if (!$templateMessage) {
                    return [
                        'success' => false,
                        'message_id' => null,
                        'error' => "Template '{$templateSid}' not found. Use a Twilio Content SID (HXxxxxx) or predefined template name.",
                    ];
                }

                $twilioMessage = $twilioClient->messages->create(
                    $toWhatsApp,
                    [
                        'from' => $fromWhatsApp,
                        'body' => $templateMessage,
                    ]
                );

                Log::info('Twilio template message sent successfully', [
                    'message_sid' => $twilioMessage->sid,
                    'template_name' => $templateSid,
                    'to' => $recipient,
                ]);
            }

            return [
                'success' => true,
                'message_id' => $twilioMessage->sid,
                'error' => null,
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio template API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];

        } catch (\Exception $e) {
            Log::error('Unexpected error sending Twilio template', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get predefined template message content
     *
     * @param string $templateName
     * @return string|null
     */
    protected function getPredefinedTemplateMessage(string $templateName): ?string
    {
        $templates = [
            'hello_world' => "👋 Hello! Welcome to our service. We're here to help you. How can we assist you today?",

            'sample_purchase_feedback' => "🛍️ Thank you for your recent purchase! We'd love to hear your feedback. How was your experience with us?",

            'sample_happy_hour_announcement' => "🎉 Special Offer! Join us for Happy Hour today! Enjoy exclusive deals and discounts. Don't miss out!",

            'sample_flight_confirmation' => "✈️ Flight Confirmation: Your booking has been confirmed. Check your email for details. Have a safe journey!",

            'sample_movie_ticket_confirmation' => "🎬 Movie Ticket Confirmed! Your booking is successful. Show this message at the counter. Enjoy the show!",

            'sample_issue_resolution' => "✅ Issue Resolved: We've addressed your concern. Thank you for your patience. Is there anything else we can help with?",

            'sample_shipping_confirmation' => "📦 Shipping Update: Your order has been dispatched and is on its way. Track your package using the link in your email.",

            'welcome_message' => "🌟 Welcome! Thank you for connecting with us. We're excited to serve you. Feel free to ask any questions!",

            'welcome_bangla_message' => "আমার মুরাদনগরে আপনাকে স্বাগতম। আপনার সকল সেবার প্রয়োজন পূরণে আমরা সর্বদা প্রস্তুত।\n\nযেকোনো প্রয়োজনে আমাদের সাথে যোগাযোগ করুন:\n📞 কল করুন: +8801234567890\n💬 অথবা হোয়াটসঅ্যাপে যোগাযোগ করুন।\n\nআমাদের সাপোর্ট টিম ২৪/৭ আপনার সেবায় নিয়োজিত।",

            'thank_you' => "🙏 Thank you for contacting us! We appreciate your message and will get back to you shortly.",

            'appointment_reminder' => "📅 Reminder: You have an appointment scheduled. Please confirm your attendance or reschedule if needed.",
        ];

        return $templates[$templateName] ?? null;
    }

    /**
     * Sanitize phone number to E.164 format
     *
     * @param string|null $number
     * @return string|null
     */
    public function sanitizePhoneNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        // Remove all non-digits
        $digitsOnly = preg_replace('/\D+/', '', $number);

        if (!$digitsOnly) {
            return null;
        }

        // Add '+' prefix if not present (E.164 format)
        if (strpos($number, '+') !== 0) {
            // If number doesn't start with country code, add default (adjust as needed)
            if (strlen($digitsOnly) === 11 && $digitsOnly[0] === '0') {
                // Convert 01604509006 to +8801604509006 (Bangladesh example)
                $digitsOnly = '88' . substr($digitsOnly, 1);
            } elseif (strlen($digitsOnly) === 10) {
                // Convert 1604509006 to +8801604509006
                $digitsOnly = '88' . $digitsOnly;
            }

            return '+' . $digitsOnly;
        }

        return '+' . $digitsOnly;
    }

    /**
     * Validate a WhatsApp number
     *
     * @param string $phoneNumber
     * @param User|null $user
     * @return array
     */
    public function validateWhatsAppNumber(string $phoneNumber, ?User $user = null): array
    {
        $sanitized = $this->sanitizePhoneNumber($phoneNumber);

        if (empty($sanitized)) {
            return [
                'valid' => false,
                'message' => 'Invalid phone number format',
                'formatted_number' => null,
            ];
        }

        if (strlen($sanitized) < 10) {
            return [
                'valid' => false,
                'message' => 'Phone number too short. Include country code.',
                'formatted_number' => null,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Phone number is valid',
            'formatted_number' => $sanitized,
        ];
    }

    /**
     * Normalize phone number (alias for sanitizePhoneNumber)
     *
     * @param string|null $number
     * @return string|null
     */
    public function normalizePhoneNumber(?string $number): ?string
    {
        return $this->sanitizePhoneNumber($number);
    }

    // ---------------------------------------------------------------
    // FAQ / Interactive Message Methods
    // ---------------------------------------------------------------

    /**
     * Get the default FAQ list.
     * Each key is the number the user types to select it.
     *
     * @return array
     */
    public function getDefaultFaqs(): array
    {
        return [
            '1' => [
                'question' => 'What are your business hours?',
                'answer'   => "🕐 *Business Hours:*\n\nMonday - Friday: 9:00 AM - 6:00 PM\nSaturday: 10:00 AM - 4:00 PM\nSunday: Closed\n\n_Reply *FAQ* anytime to see all questions._",
            ],
            '2' => [
                'question' => 'How can I track my order?',
                'answer'   => "📦 *Order Tracking:*\n\nYou can track your order:\n• Check the confirmation email sent to you\n• Visit our website and enter your Order ID\n• Reply here with your Order ID for direct help\n\n_Reply *FAQ* anytime to see all questions._",
            ],
            '3' => [
                'question' => 'What is your return policy?',
                'answer'   => "🔄 *Return Policy:*\n\n• Returns accepted within *30 days* of purchase\n• Item must be in original, unused condition\n• Contact us to initiate a return request\n• Refund processed within 5-7 business days\n\n_Reply *FAQ* anytime to see all questions._",
            ],
            '4' => [
                'question' => 'How do I contact support?',
                'answer'   => "💬 *Contact Support:*\n\n• *Chat:* Reply directly to this message\n• *Email:* support@connectdesk.com\n• *Website:* Live chat available\n\nOur team responds within *2 hours* during business hours. 🌟\n\n_Reply *FAQ* anytime to see all questions._",
            ],
            '5' => [
                'question' => 'What payment methods do you accept?',
                'answer'   => "💳 *Payment Methods:*\n\nWe accept:\n• Credit/Debit Cards (Visa, Mastercard)\n• Mobile Banking (bKash, Nagad, Rocket)\n• Bank Transfer\n• Cash on Delivery (selected areas)\n\nAll transactions are *secure & encrypted* 🔒\n\n_Reply *FAQ* anytime to see all questions._",
            ],
        ];
    }

    /**
     * Send a formatted FAQ menu message to a WhatsApp number.
     * Works in Twilio Sandbox (plain text format).
     *
     * @param string    $recipientPhone
     * @param array|null $faqs  Custom FAQ list (optional, uses default if null)
     * @param User|null  $user  Admin user for per-user Twilio credentials
     * @return array
     */
    public function sendFaqMessage(string $recipientPhone, array $faqs = null, ?User $user = null): array
    {
        $faqList = $faqs ?? $this->getDefaultFaqs();

        $message  = "📋 *Frequently Asked Questions*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Reply with the *number* of your question:\n\n";

        foreach ($faqList as $key => $item) {
            $message .= "*{$key}.* {$item['question']}\n";
        }

        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Type *FAQ* anytime to see this menu again._";

        if ($user) {
            return $this->sendMessageForUser($user, $message, $recipientPhone);
        }

        return $this->sendMessage($message, $recipientPhone);
    }

    /**
     * Get the FAQ answer for a given user reply number.
     *
     * @param string     $userReply  The message the user typed (e.g. "1", "2")
     * @param array|null $faqs       Custom FAQ list (optional)
     * @return string|null           Formatted answer, or null if not a valid choice
     */
    public function getFaqAnswer(string $userReply, array $faqs = null): ?string
    {
        $faqList = $faqs ?? $this->getDefaultFaqs();
        $choice  = trim($userReply);

        if (isset($faqList[$choice])) {
            $item = $faqList[$choice];
            return "✅ *{$item['question']}*\n\n{$item['answer']}";
        }

        return null;
    }

    /**
     * Check whether an incoming message is a FAQ trigger keyword.
     * Sending "faq", "help", "menu" etc. will trigger the FAQ menu.
     *
     * @param string $message
     * @return bool
     */
    public function isFaqTrigger(string $message): bool
    {
        $triggers = ['faq', 'help', 'menu', 'start', '?'];
        return in_array(strtolower(trim($message)), $triggers);
    }

    /**
     * Check whether an incoming message is a valid FAQ option number.
     *
     * @param string     $message
     * @param array|null $faqs
     * @return bool
     */
    public function isFaqOption(string $message, array $faqs = null): bool
    {
        $faqList = $faqs ?? $this->getDefaultFaqs();
        return isset($faqList[trim($message)]);
    }
}
