<?php

namespace App\Interfaces;

interface GameInterface
{
    public string $text{
        get;
    }
    public array $replyMarkup{
        get;
    }
    public bool $answerQuery{
        get;
    }

    public function __construct(
        string $gamer_user_id,
        string $message_id,
        string $action,
    );

    public function play();
}