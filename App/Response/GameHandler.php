<?php

namespace App\Response;

use App\Games\TicTacToe;
use App\Interfaces\GameInterface;
use App\Interfaces\HandlerInterface;

class GameHandler implements HandlerInterface
{

    public string $text = '';
    public array $replyMarkup = [];
    public bool $answerQuery = false;

    /** @var array|class-string[] */
    const array GAMES = [
        'g1' => TicTacToe::class,
    ];


    public function __construct(string $user_id, string $message_id, ?string $data = null)
    {
        [$gameMethod, $userId, $action] = explode('_', $data);

        if (!in_array($gameMethod, array_keys(self::GAMES))) {
            $this->text = 'الان نمی‌تونیم این بازی رو انجام بدیم عزیزم';
        } elseif ($user_id !== $userId) {
            $this->text = 'این بازی واسه تو نیست قشنگم';
            $this->answerQuery = true;
        } else {

            /** @var GameInterface $createClass */
            $createClass = new (self::GAMES[$gameMethod])(
                gamer_user_id: $userId,
                message_id: $message_id,
                action: $action,
            );

            $game = $createClass->play();

            $this->text = $game->text;
            $this->replyMarkup = $game->replyMarkup;
            $this->answerQuery = $game->answerQuery;
        }
        return $this;
    }


    public function processable(): bool
    {
        return !empty($this->text);
    }
}