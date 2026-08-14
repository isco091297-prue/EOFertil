<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramService
{
    public function sendMessage(string $message): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! $token || ! $chatId) {
            throw new RuntimeException(
                'Telegram no está configurado. Faltan las credenciales del bot.'
            );
        }

        $response = Http::timeout(15)
            ->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            );

        if (! $response->successful() || ! $response->json('ok')) {
            throw new RuntimeException(
                'Telegram no pudo enviar el mensaje.'
            );
        }
    }
}
