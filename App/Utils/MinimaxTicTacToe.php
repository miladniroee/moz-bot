<?php

namespace App\Utils;

class MinimaxTicTacToe
{
    private array $board;
    private string $botMark;
    private string $userMark;

    private array $patterns = [
        [0,1,2], [3,4,5], [6,7,8],
        [0,3,6], [1,4,7], [2,5,8],
        [0,4,8], [2,4,6],
    ];

    public function __construct(array $board, string $botMark, string $userMark)
    {
        $this->board = $board;
        $this->botMark = $botMark;
        $this->userMark = $userMark;
    }

    public function bestMove(): int
    {
        if (empty($this->board[4])) return 4;

        $bestScore = -INF;
        $move = -1;

        foreach ($this->emptyCells($this->board) as $cell) {


            $this->board[$cell] = $this->botMark;

            $score = $this->minimax($this->board, false);

            $this->board[$cell] = '';

            if ($score > $bestScore) {
                $bestScore = $score;
                $move = $cell;
            }
        }

        return $move;
    }

    private function minimax(array $board, bool $isMax): int
    {

        $result = $this->winner($board);
        if ($result === $this->botMark) return 10;
        if ($result === $this->userMark) return -10;


        if (empty($this->emptyCells($board))) return 0;

        if ($isMax) {

            $best = -INF;

            foreach ($this->emptyCells($board) as $cell) {
                $board[$cell] = $this->botMark;
                $score = $this->minimax($board, false);
                $board[$cell] = '';

                $best = max($best, $score);
            }

            return $best;

        } else {

            $best = INF;

            foreach ($this->emptyCells($board) as $cell) {
                $board[$cell] = $this->userMark;
                $score = $this->minimax($board, true);
                $board[$cell] = '';

                $best = min($best, $score);
            }

            return $best;
        }
    }

    private function winner(array $board): ?string
    {
        foreach ($this->patterns as $p) {
            [$a, $b, $c] = $p;
            if ($board[$a] !== '' &&
                $board[$a] === $board[$b] &&
                $board[$b] === $board[$c]) {
                return $board[$a];
            }
        }
        return null;
    }

    private function emptyCells(array $board): array
    {
        $res = [];
        foreach ($board as $i => $v) {
            if ($v === '') $res[] = $i;
        }
        return $res;
    }
}
