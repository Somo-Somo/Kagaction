<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;

class ImageReport extends Model
{
    use HasFactory;

    public static function setWeeklyImageReport()
    {
        $url = config('app.firebase_access_url');
        return new ImageMessageBuilder(
            $url . "/o/users%2F1e629af2-3210-4a8d-8849-3cee8bd9c16d%2Fimages%2Fweekly_report%2F2023-01-082023-01-14.png?alt=media&token=2bd089df-30db-4244-b9f4-205b6b7d0954",
            $url . "/o/users%2F1e629af2-3210-4a8d-8849-3cee8bd9c16d%2Fimages%2Fweekly_report%2F2023-01-082023-01-14.png?alt=media&token=2bd089df-30db-4244-b9f4-205b6b7d0954",
        );
    }
}
