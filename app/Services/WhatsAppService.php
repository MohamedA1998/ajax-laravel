<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $phoneNumberId;
    protected $baseUrl = 'https://graph.facebook.com/v21.0';

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Send a text message to a WhatsApp number.
     *
     * @param string $to The recipient's phone number.
     * @param string $message The message content.
     * @return \Illuminate\Http\Client\Response
     */
    public function sendMessage($to, $message)
    {
        $url = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

        $response = Http::withToken($this->token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $message
            ],
        ]);

        if ($response->failed()) {
            Log::error('WhatsApp Send Message Failed: ' . $response->body());
        }

        return $response;
    }

    /**
     * Send a template message.
     *
     * @param string $to
     * @param string $templateName
     * @param string $languageCode
     * @return \Illuminate\Http\Client\Response
     */
    public function sendTemplate($to, $templateName, $languageCode = 'en_US')
    {
        $url = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

        $response = Http::withToken($this->token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ]
            ],
        ]);

        if ($response->failed()) {
            Log::error('WhatsApp Send Template Failed: ' . $response->body());
        }

        return $response;
    }
}
