<?php

namespace App\Models;

use App\Database;


/**
 * @property string $id
 * @property ?string $name
 * @property ?string $username
 * @property ?string $display_name
 * @property ?string $type
 * @property ?bool $is_active
 * @property ?string $created_at
 */
class User extends Database
{
    protected string $table = 'users';

}