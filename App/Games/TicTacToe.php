<?php

namespace App\Games;

use App\Interfaces\GameInterface;
use App\Models\GameState;
use App\Utils\MinimaxTicTacToe;

class TicTacToe implements GameInterface
{

    public string $text = '';
    public array $replyMarkup = [];
    public bool $answerQuery = false;


    private ?string $botMark = null;
    private ?string $userMark = null;
    private array $game;

    const array CHECKMARKS = [
        '🍓',
        '🥭',
        '🍌',
        '🍆',
        '🥒',
    ];

    private array $winPatterns = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6]
    ];

    public function __construct(
        private readonly string $gamer_user_id,
        private readonly string $message_id,
        private readonly string $action,
    )
    {
        // create empty board
        $this->game = array_fill(0, 9, '');
    }

    public function play(): static
    {
        if ($this->hasJustStarted()) return $this;


        // Choosing Mark
        if (str_starts_with($this->action, 'st')) {

            $userMark = str_replace('st', '', $this->action);
            $botMark = $this->ChooseBotMark($userMark);

            GameState::query()->insert([
                'user_id' => $this->gamer_user_id,
                'message_id' => $this->message_id,
                'bot_mark' => $botMark,
                'user_mark' => $userMark,
                'game' => json_encode($this->game),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Select who is starting
            if (rand(0, 1) === 1) {
                $this->text = 'این بار اول تو شروع کن';
            } else {
                $this->userMark = $userMark;
                $this->botMark = $botMark;
                $this->DoBotMove();
                $this->updateGameOnDB();
                $this->text = 'اولین حرکت رو من رفتم، حالا نوبت توئه';
            }
            $this->replyMarkup = $this->getGameReplyKeyboard($this->gamer_user_id);
            return $this;
        }


        /** @var GameState $gameState */
        $gameState = GameState::query()
            ->where('user_id', $this->gamer_user_id)
            ->where('message_id', $this->message_id)
            ->first();

        if (!$gameState) {
            $this->text = 'هنوز بازی رو نتونستم بسازم، دوباره انتخاب کن.';
            $this->replyMarkup = self::emojiKeyboard($this->gamer_user_id);
            return $this;
        }

        $this->game = json_decode($gameState->game, true);
        $this->botMark = $gameState->bot_mark;
        $this->userMark = $gameState->user_mark;


        /******** VALIDATIONS *********/
        if ($this->hasWinnerBefore($gameState)) return $this;
        if ($this->inputIsWrong()) return $this;
        if ($this->selectedIsFilled()) return $this;


        // user move
        $this->game[$this->action] = $gameState->user_mark;


        if ($this->checkWinner()) return $this;

        $this->DoBotMove();

        if ($this->checkWinner()) return $this;

        $this->updateGameOnDB();

        $this->text = 'من بازی کردم، حالا نوبت توئه.';
        $this->replyMarkup = $this->getGameReplyKeyboard($this->gamer_user_id);
        return $this;
    }

    public function updateGameOnDB(string|null $winner = null): void
    {
        GameState::query()
            ->where('user_id', $this->gamer_user_id)
            ->where('message_id', $this->message_id)
            ->update([
                'game' => json_encode($this->game),
                'winner' => $winner,
            ]);
    }

    /****************** VALIDATIONS **********************/
    private function selectedIsFilled(): bool
    {
        if ($this->game[$this->action] !== '') {
            $this->text = 'این خونه قبلاً انتخاب شده.';
            $this->replyMarkup = $this->getGameReplyKeyboard($this->gamer_user_id);
            return true;
        }

        return false;
    }

    private function inputIsWrong(): bool
    {
        if (!array_key_exists($this->action, $this->game)) {
            $this->text = 'این انتخاب معتبر نیست.';
            $this->replyMarkup = $this->getGameReplyKeyboard($this->gamer_user_id);
            return true;
        }
        return false;
    }

    private function hasWinnerBefore($gameState): bool
    {
        if ($gameState->winner) {
            $this->answerQuery = true;
            if ($gameState->winner === 'user') $this->text = 'این بازی رو تو بردی قبلا.';
            if ($gameState->winner === 'bot') $this->text = 'این بازی رو من بردم.';
            if ($gameState->winner === 'draw') $this->text = 'این بازی مساوی شده.';
            return true;
        }

        return false;
    }

    /****************** VALIDATIONS **********************/


    private function ChooseBotMark(string $userMark): string
    {
        $botMark = self::CHECKMARKS[2];
        while ($botMark === $userMark):
            $botMark = self::CHECKMARKS[rand(0, count(self::CHECKMARKS) - 1)];
        endwhile;

        return $botMark;
    }

    private function checkWinner(): bool
    {
        $winner = null;
        foreach ($this->winPatterns as $pattern) {
            [$a, $b, $c] = $pattern;

            if ($this->game[$a] !== '' &&
                $this->game[$a] === $this->game[$b] &&
                $this->game[$b] === $this->game[$c]) {

                $winner = $this->game[$a] === $this->botMark ? 'bot' : 'user';
                break;
            }
        }

        if (!$winner && $this->isDraw()) $winner = 'draw';

        if ($winner) {
            $this->updateGameOnDB(winner: $winner);
            $this->replyMarkup = $this->getGameReplyKeyboard($this->gamer_user_id);

            $this->text = match ($winner) {
                'user' => 'شما برنده‌ی یک موز طلایی شدی',
                'bot' => "من بردم\n" . 'بیا قبول کن، مال این حرفا نیستی...',
                default => 'این بازی رو مساوی شدیم،' . "\n" . "برام /game رو بفرست تا دوباره بازی کنیم.",
            };

            [$user, $bot, $draw] = $this->getStats();

            $this->text .= "\n\n" . "📊 آمار بازی" .
                "\n" . "برد شما: " . $user .
                "\n" . "برد موز: " . $bot .
                "\n" . "مساوی: " . $draw;
            return true;
        }

        return false;
    }

    private function isDraw(): bool
    {
        return array_all($this->game, fn($cell) => trim($cell) !== '');
    }


    private function getGameReplyKeyboard($user_id): array
    {

        return [
            'inline_keyboard' => [
                [
                    ['text' => (!empty($this->game[0]) ? $this->game[0] : " "), 'callback_data' => "g1_{$user_id}_0"],
                    ['text' => (!empty($this->game[1]) ? $this->game[1] : " "), 'callback_data' => "g1_{$user_id}_1"],
                    ['text' => (!empty($this->game[2]) ? $this->game[2] : " "), 'callback_data' => "g1_{$user_id}_2"],
                ],
                [
                    ['text' => (!empty($this->game[3]) ? $this->game[3] : " "), 'callback_data' => "g1_{$user_id}_3"],
                    ['text' => (!empty($this->game[4]) ? $this->game[4] : " "), 'callback_data' => "g1_{$user_id}_4"],
                    ['text' => (!empty($this->game[5]) ? $this->game[5] : " "), 'callback_data' => "g1_{$user_id}_5"],
                ],
                [
                    ['text' => (!empty($this->game[6]) ? $this->game[6] : " "), 'callback_data' => "g1_{$user_id}_6"],
                    ['text' => (!empty($this->game[7]) ? $this->game[7] : " "), 'callback_data' => "g1_{$user_id}_7"],
                    ['text' => (!empty($this->game[8]) ? $this->game[8] : " "), 'callback_data' => "g1_{$user_id}_8"],
                ],
            ]
        ];

    }


    public static function emojiKeyboard(string $user_id): array
    {
        return [
            'inline_keyboard' => [array_map(function ($emoji) use ($user_id) {
                return ['text' => $emoji, 'callback_data' => "g1_{$user_id}_st$emoji"];
            }, self::CHECKMARKS)]
        ];
    }

    private function hasJustStarted(): bool
    {
        if ($this->action === 'start') {
            $this->text = "بیا با هم بازی کنیم،\nبرای شروع، انتخاب کن که می‌خوای کدوم یکی از میوه‌ها باشی...";
            $this->replyMarkup = self::emojiKeyboard($this->gamer_user_id);
            return true;
        }

        return false;
    }

    private function DoBotMove(): void
    {
        $ai = new MinimaxTicTacToe(
            board: $this->game,
            botMark: $this->botMark,
            userMark: $this->userMark,
        );

        $this->game[$ai->bestMove()] = $this->botMark;
    }

    private function getStats(): array
    {
        $stat = GameState::query()
            ->select('winner', 'COUNT(winner) as cnt')
            ->where('user_id', $this->gamer_user_id)
            ->groupBy('winner')
            ->get();

        $user = 0;
        $bot = 0;
        $draw = 0;

        foreach ($stat as $row) {
            if ($row->winner === 'user') {
                $user = $row->cnt;
            }
            if ($row->winner === 'bot') {
                $bot = $row->cnt;
            }
            if ($row->winner === 'draw') {
                $draw = $row->cnt;
            }
        }

        return [$user, $bot, $draw];
    }

}