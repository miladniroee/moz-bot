<?php

namespace App\Interfaces;

interface HandlerInterface
{
    public string $text {
        get;
    }
    public array $replyMarkup {
        get;
    }
    public bool $answerQuery {
        get;
    }

    public function __construct(string $message, string $user_id, ?string $chat_id = null);

    public function processable(): bool;
}