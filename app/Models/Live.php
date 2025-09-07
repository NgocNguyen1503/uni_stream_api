<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Live extends Model
{
    use HasFactory;

    protected $table = 'lives';

    protected $fillable = [
        'streamer_id',
        'title',
        'thumbnail',
        'time_start',
        'time_end',
        'stream_url',
        'stream_key',
        'watch_url'
    ];
}
