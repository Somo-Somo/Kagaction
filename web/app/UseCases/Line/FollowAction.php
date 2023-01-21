<?php

namespace App\UseCases\Line;

use App\Models\User;
use App\Models\Question;
use App\Models\Onboarding;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;

class FollowAction
{
    /**
     * @param LINE\LINEBot\HTTPClient\CurlHTTPClient
     */
    protected $httpClient;

    /**
     * @param LINE\LINEBot
     */
    protected $bot;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     * ユーザーが会員登録されているか確認する
     *
     * @param string $line_user_id
     * @param $event
     * @return
     */
    public function invoke($event)
    {
        $line_user_id = $event->getUserId();
        // ユーザーが既に会員登録されているか確認する
        $has_user_account = User::where('line_id', $line_user_id)->first();
        // Line上で登録済みか
        $has_line_user_account = Question::where('line_user_id', $line_user_id)->first();

        if ($has_user_account === NULL) {
            $profile = $this->bot->getProfile($line_user_id)->getJSONDecodedBody();

            // Lineユーザーの会員登録を行う
            $user = User::create([
                'name' => $profile['displayName'],
                'uuid' => (string) Str::uuid(),
                'line_id' => $line_user_id,
            ]);

            Onboarding::create([
                'user_uuid' => $user['uuid']
            ]);

            $this->bot->replyMessage(
                $event->getReplyToken(),
                Onboarding::firstGreeting()
            );
        }

        if ($has_line_user_account === NULL) {
            // Lineユーザーへの質問テーブルにも新しくレコードを保存する
            Question::create([
                'line_user_id' => $line_user_id,
                'operation_type' => 0,
                'order_number' => 1
            ]);
        }

        return;
    }
}
