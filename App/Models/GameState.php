<?php

namespace App\Models;

use App\Database;


/**
 * @property string $user_id
 * @property string $message_id
 * @property string $user_mark
 * @property string $bot_mark
 * @property string|array $game
 * @property string $winner
 * @property string $created_at
 */
class GameState extends Database
{
    protected string $table = 'game_state';

    protected string|array $primaryKey = [
        'user_id',
        'message_id',
    ];

}