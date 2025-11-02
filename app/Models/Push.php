<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    use HasFactory;

    const STATUS_WAIT = 'wait';
    const STATUS_DONE = 'done';
    const STATUS_FAIL = 'fail';

    protected $table = 'pushes';

    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id'
    ];
}
