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
        $url = config('app.firebase_access_url');
        return new ImageMessageBuilder(
            $url . "/o/users%2F1e629af2-3210-4a8d-8849-3cee8bd9c16d%2Fimages%2Fweekly_report%2F2023-01-082023-01-14.png?alt=media&token=2bd089df-30db-4244-b9f4-205b6b7d0954",
            $url . "/o/users%2F1e629af2-3210-4a8d-8849-3cee8bd9c16d%2Fimages%2Fweekly_report%2F2023-01-082023-01-14.png?alt=media&token=2bd089df-30db-4244-b9f4-205b6b7d0954",
        );
    }
}
