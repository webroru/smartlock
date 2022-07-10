<?php

declare(strict_types=1);

namespace App\Services;

use App\Logger;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram as TelegramClient;

class Telegram
{
    public function __construct(private readonly TelegramClient $telegram)
    {
    }

    public function sendMessageToChanel(string $chatId, string $message): void
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];
        $result = Request::sendMessage($data);

        if (!$result->isOk()) {
            Logger::error('Can not send message to Telegram');
        }
    }
}
