<?php

namespace App;

final readonly class BotService
{
    public static function BASE(): string
    {
        $endpoint = 'https://api.telegram.org';
        if (config('BOT_PROVIDER') === 'BALE') {
            $endpoint = 'https://tapi.bale.ai';
        }
        return $endpoint . '/bot' . config('BOT_API_KEY');
    }

    public static function sendMessage(array $data): bool|string
    {
        return self::request(self::BASE() . '/sendMessage', $data);
    }

    public static function sendVideo(array $data): bool|string
    {
        return self::request(self::BASE() . '/sendVideo', $data);
    }

    public static function editMessage(array $data): bool|string
    {
        return self::request(self::BASE() . '/editMessageText', $data);
    }

    public static function answerCallbackQuery(array $data): bool|string
    {
        return self::request(self::BASE() . '/answerCallbackQuery', $data);
    }

    public static function leaveChat(array $data): bool|string
    {
        return self::request(self::BASE() . '/leaveChat', $data);
    }

    private static function request(string $url, array $data): bool|string
    {

        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_POST, true);
        curl_setopt($ch1, CURLOPT_HEADER, true);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch1);
        curl_close($ch1);

        return $response;
    }
}