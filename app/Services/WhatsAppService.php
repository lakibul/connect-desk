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
        // You'll need to set these in your .env file
        $this->apiUrl = 'https://graph.facebook.com/v18.0/';
        $this->accessToken = config('services.whatsapp.access_token');
        $this->businessPhoneNumberId = config('services.whatsapp.phone_number_id');
        $this->targetPhoneNumber = '+8801604509006'; // Your specified number
    }

    /**
     * Send a WhatsApp message
     */
    public function sendMessage(string $message, string $recipientPhone = null): bool
    {
        try {
            $recipient = $recipientPhone ?? $this->targetPhoneNumber;

            // Log the attempt
            Log::info('Attempting to send WhatsApp message', [
                'recipient' => $recipient,
                'message_preview' => substr($message, 0, 100) . '...',
                'access_token_exists' => !empty($this->accessToken),
                'phone_number_id_exists' => !empty($this->businessPhoneNumberId)
            ]);

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
                    'body' => $message
                ]
            ];            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
            dd($responseBody);

            if ($statusCode === 200) {
                return $responseBody;
                // Log::info('WhatsApp message sent successfully', [
                //     'recipient' => $recipient,
                //     'message_id' => $responseBody['messages'][0]['id'] ?? null
                // ]);
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

    /**
     * Send a template message (for marketing/notifications)
     */
    public function sendTemplateMessage(string $templateName, array $parameters = [], string $recipientPhone = null): bool
    {
        try {
            $recipient = $recipientPhone ?? $this->targetPhoneNumber;

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
