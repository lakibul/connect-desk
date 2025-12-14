<?php

namespace App\Services;

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
     * Send a WhatsApp message
     */
    public function sendMessage(string $message, string $recipientPhone = null): bool
    {
        try {
            $recipient = $this->sanitizePhoneNumber($recipientPhone ?? $this->targetPhoneNumber);

            if (empty($recipient)) {
                Log::error('WhatsApp recipient phone number missing or invalid');
                return false;
            }
            if (empty($this->accessToken) || empty($this->businessPhoneNumberId)) {
                Log::error('WhatsApp credentials not configured');
                return false;
            }

            $url = $this->apiUrl . $this->businessPhoneNumberId . '/messages';

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $message
                ]
            ];

            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode === 200) {
                Log::info('WhatsApp message sent successfully', [
                    'recipient' => $recipient,
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
        try {
            $recipient = $this->sanitizePhoneNumber($recipientPhone ?? $this->targetPhoneNumber);

            if (empty($recipient)) {
                Log::error('WhatsApp template recipient phone number missing or invalid');
                return false;
            }

            $url = $this->apiUrl . $this->businessPhoneNumberId . '/messages';

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
                    'Authorization' => 'Bearer ' . $this->accessToken,
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

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(string $signature, string $payload): bool
    {
        $webhookSecret = config('services.whatsapp.webhook_secret');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }
}
