<?php

namespace App\Services;

use App\Models\Setting;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Log;

class TelegramNotify
{
    /**
     * Send Telegram notification
     *
     * @param string|null $msg
     * @param string|null $chat_id
     * @return mixed|false
     */
    public static function T_NOTIFY($msg = null, $chat_id = null)
    {
        if (!$chat_id) {
            // Try to get chat_id from multiple sources
            $chat_id = config('values.telegram_chat_id') ?? Setting::get('telegram_chat_id') ?? env('TELEGRAM_CHAT_ID');
        }

        // Telegram Bot Token - try multiple sources
        $botToken = Setting::get('telegram_bot_token')
            ?? config('values.telegram_bot_token')
            ?? env('TELEGRAM_BOT_TOKEN');

        if (!$botToken) {
            Log::warning('Telegram bot token not configured');
            return false;
        }

        if (!$chat_id) {
            Log::warning('Telegram chat_id not configured');
            return false;
        }

        $telegram = new Api($botToken);

        if (!$msg) {
            $msg = "This is a test SMS \n\n\n <i>Italic</i> \n <u>Underline</u> \n <s>Strikethrough</s> \n\n <pre>code block</pre> \n\n 😊 😎 😄 — Smiling faces \n\n 🎉 🎊 ✨ — Celebration \n\n 🔥 🚀 🌟 — Excitement or success \n\n ✅ ❌ ⚠️ — Checkmarks and warnings";
        }

        try {
            $response = $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'HTML'
            ]);

            return $response;
        } catch (\Throwable $exception) {
            Log::error('Telegram notification failed', [
                'chat_id' => $chat_id,
                'message' => $msg,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}

