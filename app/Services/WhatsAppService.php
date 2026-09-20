<?php

namespace App\Services;

class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        // WhatsApp module disabled
        return false;
    }
}
