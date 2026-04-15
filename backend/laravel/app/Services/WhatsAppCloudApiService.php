<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    private string $phoneNumberId;
    private string $accessToken;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', env('WA_PHONE_NUMBER_ID'));
        $this->accessToken = config('services.whatsapp.access_token', env('WA_ACCESS_TOKEN'));
        $this->apiVersion = config('services.whatsapp.api_version', env('WA_API_VERSION', 'v19.0'));
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}";
    }

    public function sendText(string $to, string $body): void
    {
        $this->post('/messages', [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'text',
            'text'              => [
                'preview_url' => false,
                'body'        => $body,
            ],
        ]);
    }

    public function sendList(string $to, string $header, string $body, array $sections): void
    {
        $this->post('/messages', [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'interactive',
            'interactive'       => [
                'type'   => 'list',
                'header' => [
                    'type' => 'text',
                    'text' => $header,
                ],
                'body'   => [
                    'text' => $body,
                ],
                'action' => [
                    'button'   => 'Select Option',
                    'sections' => $sections,
                ],
            ],
        ]);
    }

    public function sendButtons(string $to, string $body, array $buttons): void
    {
        $actionButtons = array_map(function ($btn, $index) {
            return [
                'type'  => 'reply',
                'reply' => [
                    'id'    => $btn['id'] ?? 'btn_' . $index,
                    'title' => $btn['title'],
                ],
            ];
        }, $buttons, array_keys($buttons));

        $this->post('/messages', [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'interactive',
            'interactive'       => [
                'type'   => 'button',
                'body'   => [
                    'text' => $body,
                ],
                'action' => [
                    'buttons' => $actionButtons,
                ],
            ],
        ]);
    }

    public function downloadMedia(string $mediaId, string $destPath): ?string
    {
        try {
            // Step 1: Get media URL
            $response = Http::withToken($this->accessToken)
                ->get("https://graph.facebook.com/{$this->apiVersion}/{$mediaId}");

            if (!$response->successful()) {
                Log::error('WhatsApp Media URL fetch failed', ['response' => $response->body()]);
                return null;
            }

            $mediaUrl = $response->json('url');
            if (!$mediaUrl) {
                return null;
            }

            // Step 2: Download the actual content
            $fileResponse = Http::withToken($this->accessToken)->get($mediaUrl);
            
            if (!$fileResponse->successful()) {
                Log::error('WhatsApp Media download failed', ['response' => $fileResponse->body()]);
                return null;
            }

            // Step 3: Store locally
            // Default storage inside storage/app/public or storage/app depending on requirements.
            // Using standard lead document storage which seems to be public disk or local.
            Storage::disk('local')->put($destPath, $fileResponse->body());
            
            return $destPath;
        } catch (\Exception $e) {
            Log::error('WhatsApp Media download exception: ' . $e->getMessage());
            return null;
        }
    }

    private function post(string $endpoint, array $data): void
    {
        try {
            if (empty($this->phoneNumberId) || empty($this->accessToken)) {
                Log::warning('WhatsApp API credentials are not set. Message not sent.');
                return;
            }

            $response = Http::withToken($this->accessToken)
                ->post($this->baseUrl . $endpoint, $data);

            if (!$response->successful()) {
                Log::error('WhatsApp API sending failed', ['response' => $response->body(), 'data' => $data]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp API sending exception: ' . $e->getMessage());
        }
    }
}
