<?php

namespace App\UseCases\Line;

use App\Models\Condition;
use App\Models\SelfCheckNotification;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use DateTime;


class SelfCheckNotificationAction
{
    /**
     * @param LINE\LINEBot\HTTPClient\CurlHTTPClient
     */
    protected $httpClient;

    /**
     * @param LINE\LINEBot
     */
    protected $bot;

    /**
     */
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     * 該当するユーザーに振り返りの通知を送る
     *
     * @return
     */
    public function invoke()
    {
        Log::info('success');
        $datetime = new DateTime();
        $time = $datetime->format('H') . ':00:00';
        $recive_notifications = SelfCheckNotification::where('time', $time)->get();
        if (count($recive_notifications) > 0) {
            Log::info('has');
            foreach ($recive_notifications as  $recive_notification) {
                $this->bot->pushMessage(
                    $recive_notification->user->line_id,
                    Condition::askCondition($recive_notification->user->name)
                );
            }
        }
        return;
    }
}