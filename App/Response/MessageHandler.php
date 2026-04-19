<?php

namespace App\Response;

use App\Interfaces\HandlerInterface;
use App\Models\User;
use App\Utils\PersonalizeText;

class MessageHandler implements HandlerInterface
{
    private array $randomTexts;

    public function __construct(string $message, string $userId, ?string $chat_id = null)
    {
        $this->randomTexts = require_once text_dir('Random');
        $this->text = self::personalize($userId, $chat_id);
    }

    private function personalize(string $userId, ?string $chatId): string
    {
        $displayName = null;

        if ($user = $this->getUser($userId)) $displayName = $user->display_name;

        do {
            $random = $this->randomTexts[array_rand($this->randomTexts)];
        } while (str_contains($random, ':name') && $displayName === null);

        if ($displayName) $random = str_replace(':name', $displayName, $random);

        if (class_exists(\App\Utils\PersonalizeText::class) && property_exists(\App\Utils\PersonalizeText::class, 'make')) {
            $random = PersonalizeText::make($userId,$chatId,$random);
        }

        return $random;
    }

    public string $text {
        get {
            return $this->text;
        }
    }
    public array $replyMarkup = [] {
        get {
            return $this->replyMarkup;
        }
    }

    public function processable(): bool
    {
        return $this->text;
    }

    public bool $answerQuery = false {
        get {
            return $this->answerQuery;
        }
    }

    private function getUser(string $userId): object|false
    {
        return User::query()
            ->select('id','display_name')
            ->find($userId);
    }
}