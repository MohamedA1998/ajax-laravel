<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Verify the webhook (GET request from Meta).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');
        
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verifyToken) {
                return response($challenge, 200);
            }
            return response('Forbidden', 403);
        }

        return response('Bad Request', 400);
    }

    /**
     * Handle incoming webhook events (POST request).
     */
    public function handleWebhook(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('WhatsApp Webhook:', $request->all());

        $body = $request->all();

        // Check if it's a WhatsApp object
        if (isset($body['object']) && $body['object'] === 'whatsapp_business_account') {
            if (isset($body['entry']) && isset($body['entry'][0]['changes'])) {
                foreach ($body['entry'][0]['changes'] as $change) {
                    $value = $change['value'];

                    if (isset($value['messages'])) {
                        foreach ($value['messages'] as $message) {
                            $from = $message['from']; // Sender's phone number
                            $msgBody = $message['text']['body'] ?? ''; // Message content
                            
                            // Example: Echo the message back using the service
                            // In a real app, you would process the message here
                            if (!empty($msgBody)) {
                                $this->whatsAppService->sendMessage($from, "You said: " . $msgBody);
                            }
                        }
                    }
                }
            }
            return response('EVENT_RECEIVED', 200);
        }

        return response('Not Found', 404);
    }
}
