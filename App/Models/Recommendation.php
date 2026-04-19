<?php

namespace App\Models;

use App\Database;


/**
 * @property int $id
 * @property string $text
 * @property string $created_at
 */
class Recommendation extends Database
{
    protected string $table = 'recommendation';
}