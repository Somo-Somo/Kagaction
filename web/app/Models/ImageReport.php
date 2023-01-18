<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;

class ImageReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_uuid',
        'token',
        'start_day',
        'end_day',
        'created_at'
    ];

    /**
     * 週間レポートの画像URLを持ってくる
     *
     * @param string $user_uuid
     * @param string $start_day
     * @param string $end_day
     * @return string $image_url
     */
    public static function getWeeklyImageReportUrl(string $user_uuid, string $start_day, string $end_day)
    {
        $image_report = ImageReport::where('user_uuid', $user_uuid)
            ->where('start_day', $start_day)
            ->where('end_day', $end_day)
            ->first();
        $url = config('app.firebase_access_url');
        $image_url = $image_report ?
            $url . "/o/users%2F" . $user_uuid . "%2Fimages%2Fweekly_report%2F" . $start_day . $end_day . ".png?alt=media&token=" . $image_report->token
            : null;
        return $image_url;
    }
}
