<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public static function setWeeklyImageReport(string $user_uuid)
    {
        Carbon::setWeekStartsAt(Carbon::SUNDAY); // 週の最初を日曜日に設定
        Carbon::setWeekEndsAt(Carbon::SATURDAY); // 週の最後を土曜日に設定
        $today = Carbon::today();
        $week_start_day = $today->copy()->startOfWeek()->subWeek(1)->toDateString();
        $week_end_day = $today->copy()->endOfWeek()->subWeek(1)->toDateString();
        $image_report = ImageReport::where('user_uuid', $user_uuid)
            ->where('start_day', $week_start_day)
            ->where('end_day', $week_end_day)
            ->first();
        $url = config('app.firebase_access_url');
        $image_url = $url . "/o/users%2F" . $user_uuid . "%2Fimages%2Fweekly_report%2F" . $week_start_day . $week_end_day . ".png?alt=media&token=" . $image_report->token;
        return new ImageMessageBuilder(
            $image_url,
            $image_url
        );
    }
}
