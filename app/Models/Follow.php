<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    const OWNER = 2;
    const FOLLOWED = 1;
    const NOT_FOLLOWED = 0;

    use HasFactory;

    protected $table = 'follows';

    protected $fillable = [
        'user_id',
        'follow_id',
    ];
}
