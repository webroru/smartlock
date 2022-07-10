<?php

namespace App;

use Longman\TelegramBot\Telegram;

class Logger
{
    public static function log(string $message): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/../logs/app.log', "$date $message\n", FILE_APPEND);
    }

    public static function error(string $message): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/../logs/error.log', "$date $message\n", FILE_APPEND);
    }

    public static function critical(string $message): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/../logs/error.log', "$date $message\n", FILE_APPEND);

        $telegram = new Telegram(getenv('TELEGRAM_BOT_API_KEY'), getenv('TELEGRAM_BOT_USERNAME'));
        $telegramService = new \App\Services\Telegram($telegram);
        $telegramService->sendMessageToChanel(getenv('TELEGRAM_CHANEL_ID'), $message);
    }
}
