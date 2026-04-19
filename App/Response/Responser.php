<?php

namespace App\Response;

use App\BotService;

class Responser
{

    const array IGNORE_WORDS = [
        'اموزش',
        'آموزش',
        'موزاییک',
        'موزیک',
        'موزه',
        "موزدور"
    ];

    public function __construct(
        private readonly string  $message,
        private readonly string  $user_id,
        private readonly string  $chat_id,
        private readonly string  $message_id,
        private readonly ?string $data = null,
        private readonly ?string $query_id = null,
        private ?string          $text = null,
        private array            $reply_markup = [],
        private array            $sendingData = [],
        private string           $sendingMethod = '',
    )
    {
        return $this;
    }

    public function message(): static
    {

        if (str_contains($this->message, '/')) {
            $command = new CommandHandler($this->message, $this->user_id, $this->chat_id);
            if ($command->processable()) {
                $this->text = $command->text;
                $this->reply_markup = $command->replyMarkup;
            }
        }

        $foundIgnoreWord = false;
        foreach (self::IGNORE_WORDS as $word) {
            if (str_contains($this->message, $word)) {
                $foundIgnoreWord = true;
                break;
            }
        }

        if (!$this->text && !$foundIgnoreWord && (str_contains($this->message, 'موز') || preg_match('/م+و+ز/u', $this->message))) {
            $message = new MessageHandler($this->message, $this->user_id, $this->chat_id);
            if ($message->processable()) {
                $this->text = $message->text;
                $this->reply_markup = $message->replyMarkup;
            }
        }


        if (!$this->text) exit();


        $data = [
            'chat_id' => $this->chat_id,
            'text' => $this->text,
            'reply_to_message_id' => $this->message_id,
        ];

        if (!empty($this->reply_markup)) $data['reply_markup'] = $this->reply_markup;

        $this->sendingData = $data;
        $this->sendingMethod = 'sendMessage';

        return $this;
    }

    public function callback(): static
    {
        $game = new GameHandler($this->user_id, $this->message_id, $this->data);

        if ($game->answerQuery):
            $data = [
                'callback_query_id' => $this->query_id,
                'text' => $this->text,
                'show_alert' => true,
            ];

            $this->sendingMethod = 'answerCallbackQuery';
        else:
            $data = [
                'chat_id' => $this->chat_id,
                'message_id' => $this->message_id,
                'text' => $game->text,
            ];

            if (!empty($game->replyMarkup)) {
                $data['reply_markup'] = $game->replyMarkup;
            }

            $this->sendingMethod = 'editMessage';
        endif;

        $this->sendingData = $data;

        return $this;
    }

    public function send(): void
    {
        BotService::{$this->sendingMethod}($this->sendingData);
    }

}