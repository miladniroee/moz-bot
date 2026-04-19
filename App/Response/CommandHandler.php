<?php

namespace App\Response;

use App\Interfaces\HandlerInterface;
use App\Models\Recommendation;

class CommandHandler implements HandlerInterface
{
    const array ALLOWED_COMMANDS = [
        'motivate' => '/mozivate',
        'idiom' => '/idiom',
        'suggest' => '/suggest',
        'game' => '/game',
    ];


    public string $text = '' {
        get {
            return $this->text;
        }
    }
    public array $replyMarkup = [] {
        get {
            return $this->replyMarkup;
        }
    }
    public bool $answerQuery = false {
        get {
            return $this->answerQuery;
        }
    }

    private string $allowed;


    public function __construct(public string $message, public string $user_id, ?string $chat_id = null)
    {

        foreach (self::ALLOWED_COMMANDS as $method => $allowed) {
            if (str_contains($this->message, $allowed)) {
                $this->allowed = $allowed;
                $this->{$method}();
            }
        }
    }

    public function motivate(): void
    {
        $motivate = require_once text_dir('Motivate');

        $this->text = $motivate[array_rand($motivate)] . "\n\n 🍌";
    }

    public function idiom(): void
    {
        $motivate = require_once text_dir('Idiom');

        $this->text = $motivate[array_rand($motivate)] . "\n🍌🍌";
    }

    public function suggest(): void
    {
        $text = trim(str_replace($this->allowed, '', $this->message));

        if (empty($text)):
            $this->text = 'باید در ادامه‌ی کامند یه چیزی بنویسی که من اضافه‌ش کنم.';
            return;
        endif;

        if (count(explode(' ', $text)) < 3):
            $this->text = "حداقل باید سه تا کلمه باشه.";
            return;
        endif;

        Recommendation::query()->insert([
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->text = "اضافه شد.\n بررسی می‌کنم اگر خیلی ناجور نبود اضافه میشه به لیست.";
    }

    public function game(): void
    {

        $this->text = 'چی دوست داری بازی کنیم؟';


        $replyMarkup = [
            [['text' => 'Tic Tac Toe', 'callback_data' => "g1_{$this->user_id}_start"]],
        ];

        $this->replyMarkup = [
            'inline_keyboard' => $replyMarkup,
        ];
    }


    public function processable(): bool
    {
        return (bool)$this->text;
    }
}