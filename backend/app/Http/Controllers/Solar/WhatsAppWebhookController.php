<?php

namespace App\Http\Controllers\Solar;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    private WhatsAppChatbotService $chatbotService;

    public function __construct(WhatsAppChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Webhook verification for Meta WhatsApp API setup
     */
    public function verify(Request $request): Response
    {
        $verifyToken = config('services.whatsapp.verify_token', env('WA_VERIFY_TOKEN'));

        $hubMode = $request->query('hub_mode');
        $hubVerifyToken = $request->query('hub_verify_token');
        $hubChallenge = $request->query('hub_challenge');

        if ($hubMode === 'subscribe' && $hubVerifyToken === $verifyToken) {
            return response($hubChallenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming messages from Meta WhatsApp API
     */
    public function handle(Request $request): Response
    {
        try {
            $data = $request->all();

            // Structure expected:
            // "entry" -> 0 -> "changes" -> 0 -> "value" -> "messages" -> 0 -> "from" & "text"/"interactive" etc
            if (!empty($data['entry'][0]['changes'][0]['value']['messages'])) {
                $messages = $data['entry'][0]['changes'][0]['value']['messages'];
                
                // Usually just 1 message per webhook
                foreach ($messages as $message) {
                    $from = $message['from'];
                    $this->chatbotService->handle($from, $message);
                }
            }

            // Acknowledge receipt to Meta immediately so it stops retrying
            return response('EVENT_RECEIVED', 200);

        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Still return 200 so Meta doesn't retry infinitely on hard errors
            return response('EVENT_RECEIVED', 200);
        }
    }
}
