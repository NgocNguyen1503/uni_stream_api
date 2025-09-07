<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class Common
{
    /**
     * Update live info
     * @param mixed $live
     */
    public static function updateLiveInfor($live)
    {
        // Set the thumbnail URL
        $live->thumbnail = env('APP_URL') . '/uploads/thumbnails/' . $live->thumbnail;
        // Parse time ago
        // Carbon::setLocale('vi');
        $live->time_start = Common::timeAgo($live->time_start);
        // Return the updated live info
        return $live;
    }

    public static function timeAgo($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }
}