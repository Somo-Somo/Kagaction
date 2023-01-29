<?php

namespace App\UseCases\Line;

use App\Models\User;
use Carbon\Carbon;
use DateTime;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class SendKaizenFormAction
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
        Carbon::setWeekStartsAt(Carbon::SUNDAY); // 週の最初を日曜日に設定
        Carbon::setWeekEndsAt(Carbon::SATURDAY); // 週の最後を土曜日に設定
        $today = Carbon::today();
        $users = User::whereDate('created_at', $today->copy()->subWeek())->get();
        Log::debug((array)$users);
        if (count($users) > 0) {
            foreach ($users as $key => $user) {
                $multi_message = new MultiMessageBuilder();
                $multi_message->add(new TextMessageBuilder(
                    '1週間アガトンα版をご利用いただきありがとうございます。'
                        . "\n" . '使いづらかった部分や改善点がございましたらこちらのフォームの詳細からご入力いただけると嬉しいです！'
                ));
                $multi_message->add(new TextMessageBuilder('https://forms.gle/GaK15x3oDPnDUNCw8'));
                $this->bot->pushMessage($user->line_id, $multi_message);
            }
        }
        return;
    }
}
