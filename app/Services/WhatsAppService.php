<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $apiUrl;
    protected $accessToken;
    protected $businessPhoneNumberId;
    protected $targetPhoneNumber;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = 'https://graph.facebook.com/v18.0/';
        $this->accessToken = config('services.whatsapp.access_token');
        $this->businessPhoneNumberId = config('services.whatsapp.phone_number_id');
        $this->targetPhoneNumber = $this->sanitizePhoneNumber(
            config('services.whatsapp.target_phone_number')
        );
    }

    /**
     * Send a WhatsApp message to the target phone number
     *
     * @param string $message The message content to send
     * @param string|null $recipientPhone The recipient phone number (defaults to target phone from config)
     * @param string|null $senderPhone The sender's phone number (included in message metadata)
     * @return bool True if message was sent successfully, false otherwise
     */
    public function sendMessage(string $message, string $recipientPhone = null, string $senderPhone = null): bool
    {
        return $this->sendMessageWithCredentials(
            $message,
            $recipientPhone,
            $senderPhone,
            $this->accessToken,
            $this->businessPhoneNumberId
        );
    }

    public function sendMessageForUser(User $user, string $message, string $recipientPhone = null): bool
    {
        return $this->sendMessageWithCredentials(
            $message,
            $recipientPhone,
            $user->phone_number,
            $this->resolveAccessToken($user),
            $this->resolvePhoneNumberId($user)
        );
    }

    public function sendTemplateMessageForUser(User $user, string $templateName, array $parameters = [], string $recipientPhone = null): bool
    {
        return $this->sendTemplateMessageWithCredentials(
            $templateName,
            $parameters,
            $recipientPhone,
            $this->resolveAccessToken($user),
            $this->resolvePhoneNumberId($user)
        );
    }

    public function normalizePhoneNumber(?string $number): ?string
    {
        return $this->sanitizePhoneNumber($number);
    }

    protected function sanitizePhoneNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        // Remove all non-digits
        $digitsOnly = preg_replace('/\D+/', '', $number);

        if (!$digitsOnly) {
            return null;
        }

        // Ensure proper formatting with country code
        // Format: 8801604509006 (88 = Bangladesh country code)
        if (strlen($digitsOnly) === 11 && $digitsOnly[0] === '0') {
            // Convert 01604509006 to 8801604509006
            $digitsOnly = '88' . substr($digitsOnly, 1);
        } elseif (strlen($digitsOnly) === 10) {
            // Convert 1604509006 to 8801604509006
            $digitsOnly = '88' . $digitsOnly;
        }

        return $digitsOnly;
    }

    /**
     * Send a template message (for marketing/notifications)
     */
    public function sendTemplateMessage(string $templateName, array $parameters = [], string $recipientPhone = null): bool
    {
        return $this->sendTemplateMessageWithCredentials(
            $templateName,
            $parameters,
            $recipientPhone,
            $this->accessToken,
            $this->businessPhoneNumberId
        );
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(string $signature, string $payload): bool
    {
        $webhookSecret = config('services.whatsapp.webhook_secret');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function sendMessageWithCredentials(
        string $message,
        ?string $recipientPhone,
        ?string $senderPhone,
        ?string $accessToken,
        ?string $phoneNumberId
    ): bool {
        try {
            $recipient = $this->sanitizePhoneNumber($recipientPhone ?? $this->targetPhoneNumber);

            if (empty($recipient)) {
                Log::error('WhatsApp recipient phone number missing or invalid');
                return false;
            }
            if (empty($accessToken) || empty($phoneNumberId)) {
                Log::error('WhatsApp credentials not configured');
                return false;
            }

            $url = $this->apiUrl . $phoneNumberId . '/messages';

            // Sender phone is already included in the message content by MessageController
            $messageBody = $message;
            if (!empty($senderPhone)) {
                // Placeholder for future sender-context metadata
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $messageBody
                ]
            ];

            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode === 200) {
                Log::info('WhatsApp message sent successfully', [
                    'recipient' => $recipient,
                    'sender_phone' => $senderPhone ?? 'unknown',
                    'message_id' => $responseBody['messages'][0]['id'] ?? null
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message', [
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);
            return false;

        } catch (RequestException $e) {
            Log::error('WhatsApp API request failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error sending WhatsApp message', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function sendTemplateMessageWithCredentials(
        string $templateName,
        array $parameters,
        ?string $recipientPhone,
        ?string $accessToken,
        ?string $phoneNumberId
    ): bool {
        try {
            $recipient = $this->sanitizePhoneNumber($recipientPhone ?? $this->targetPhoneNumber);

            if (empty($recipient)) {
                Log::error('WhatsApp template recipient phone number missing or invalid');
                return false;
            }
            if (empty($accessToken) || empty($phoneNumberId)) {
                Log::error('WhatsApp credentials not configured');
                return false;
            }

            $url = $this->apiUrl . $phoneNumberId . '/messages';

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'en_US'
                    ]
                ]
            ];

            if (!empty($parameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => $parameters
                    ]
                ];
            }

            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode === 200) {
                Log::info('WhatsApp template message sent successfully', [
                    'recipient' => $recipient,
                    'template' => $templateName,
                    'message_id' => $responseBody['messages'][0]['id'] ?? null
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp template message', [
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);
            return false;

        } catch (RequestException $e) {
            Log::error('WhatsApp template API request failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error sending WhatsApp template message', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function resolveAccessToken(?User $user): ?string
    {
        if ($user && !empty($user->whatsapp_access_token)) {
            return $user->whatsapp_access_token;
        }

        return $this->accessToken;
    }

    protected function resolvePhoneNumberId(?User $user): ?string
    {
        if ($user && !empty($user->whatsapp_phone_number_id)) {
            return $user->whatsapp_phone_number_id;
        }

        return $this->businessPhoneNumberId;
    }

    /**
     * Check if a WhatsApp number exists and is valid
     * 
     * @param string $phoneNumber The phone number to validate
     * @param User|null $user The user whose credentials to use
     * @return array Returns ['exists' => bool, 'message' => string]
     */
    public function validateWhatsAppNumber(string $phoneNumber, ?User $user = null): array
    {
        try {
            $sanitizedNumber = $this->sanitizePhoneNumber($phoneNumber);
            
            if (empty($sanitizedNumber)) {
                return [
                    'exists' => false,
                    'message' => 'Invalid phone number format. Please enter a valid number.'
                ];
            }

            // Check if number has country code
            if (strlen($sanitizedNumber) < 10) {
                return [
                    'exists' => false,
                    'message' => 'Phone number is too short. Include country code (e.g., 8801XXXXXXXXX)'
                ];
            }

            $accessToken = $this->resolveAccessToken($user);
            $phoneNumberId = $this->resolvePhoneNumberId($user);

            if (empty($accessToken) || empty($phoneNumberId)) {
                return [
                    'exists' => false,
                    'message' => 'WhatsApp credentials not configured. Please configure in Settings.'
                ];
            }

            // For now, we'll consider the number valid if it's properly formatted
            // WhatsApp will reject invalid numbers when we try to send
            return [
                'exists' => true,
                'message' => 'Number appears valid',
                'formatted_number' => $sanitizedNumber
            ];

        } catch (\Exception $e) {
            Log::error('Error validating WhatsApp number', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber
            ]);
            
            return [
                'exists' => false,
                'message' => 'Unable to validate number. Please try again.'
            ];
        }
    }
}
