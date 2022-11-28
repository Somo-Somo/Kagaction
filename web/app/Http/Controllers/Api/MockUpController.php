<?php

namespace App\Http\Controllers\Api;

use App\Models\MockUp;
use App\Http\Controllers\Controller;
use App\Services\LineBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;

class MockUpController extends Controller
{
    /**
     * @param LineBotService
     */
    protected $line_bot_service;

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
        $this->line_bot_service = new LineBotService();
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     * When a message is sent to the official Line account,
     * The API is called by LINE WebHook and this method is called.
     *
     * Lineの公式アカウントにメッセージが送られたときに
     * LINE Web HookにてAPIがCallされこのメソッドが呼ばれる
     *
     * @param Request $request
     */
    public function reply(Request $request)
    {
        Log::debug('a');

        $status_code = $this->line_bot_service->eventHandler($request);

        // リクエストをEventオブジェクトに変換する
        $events = $this->bot->parseEventRequest($request->getContent(), $request->header('x-line-signature'));

        foreach ($events as $event) {
            if ($event->getType() === 'follow') {
            } else if ($event->getType() === 'message') {
                if ($event->getText() === '記録する') {
                    $user_name = $this->bot->getProfile($event->getUserId())->getJSONDecodedBody()['displayName'];
                    $test = $this->bot->replyMessage(
                        $event->getReplyToken(),
                        MockUp::askFeeling($user_name)
                    );
                    Log::debug((array)$test);
                }
            }
        }

        Log::debug($status_code);

        return response('', $status_code, []);
    }
}
