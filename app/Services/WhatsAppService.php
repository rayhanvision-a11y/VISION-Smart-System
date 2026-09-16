<?php

namespace App\Services;

class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        $phone = preg_replace('/\D/', '', $phone);
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $client->post('http://127.0.0.1:2785/api/messages/send', [
                'json' => ['to' => $phone, 'message' => $message],
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::warning('WhatsApp send failed: ' . $e->getMessage());
            return false;
        }
    }
}
