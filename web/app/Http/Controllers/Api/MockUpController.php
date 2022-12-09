<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\MockUp;
use App\Models\Question;
use App\Models\Condition;
use App\Http\Controllers\Controller;
use App\Models\Diary;
use App\UseCases\Line\FollowAction;
use App\Services\LineBotService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

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
    public function reply(Request $request, FollowAction $follow_action,)
    {
        $status_code = $this->line_bot_service->eventHandler($request);

        // リクエストをEventオブジェクトに変換する
        $events = $this->bot->parseEventRequest($request->getContent(), $request->header('x-line-signature'));

        foreach ($events as $event) {
            if ($event->getType() === 'follow') {
                $follow_action->invoke($event->getUserId());
            } else if ($event->getType() === 'message') {
                $user = User::where('line_id', $event->getUserId())->first();
                $question = Question::where('line_user_id', $event->getUserId())->first();
                Log::debug((array)$question);
                if ($event->getText() === '話す') {
                    $user_name = $this->bot->getProfile($event->getUserId())->getJSONDecodedBody()['displayName'];
                    $question->update(['operation_type' => 1, 'order_number' => 1]);
                    $this->bot->replyMessage(
                        $event->getReplyToken(),
                        Condition::askCondition($user_name)
                    );
                }
                if ($question->operation_type === 1) {
                    if ($question->order_number === 1) {
                        if ($event->getText() === '絶好調' || $event->getText() === '好調') {
                            $date_time = new DateTime();
                            // 保存
                            $condition = Condition::create([
                                'user_uuid' => $user->uuid,
                                'evaluation' => Condition::EVALUATION[$event->getText()],
                                'date' => $date_time->format('Y-m-d'),
                                'time' => $date_time->format('H:i:s')
                            ]);
                            $this->bot->replyMessage($event->getReplyToken(), Condition::askWhatIsHappened($user, $event->getText()));
                            $question->update(['condition_id' => $condition->id, 'order_number' => 2]);
                        } else if ($event->getText() === 'まあまあ') {
                            # code...
                        } else if ($event->getText() === '不調') {
                            # code...
                        } else if ($event->getText() === '絶不調') {
                            # code...
                        }
                    } else if ($question->order_number === 2) {
                        if ($event->getText() === 'ある') {
                            $this->bot->replyMessage($event->getReplyToken(), Question::pleaseWriteWhatHappened($question));
                        } else if ($event->getText() === 'ない') {
                            $this->bot->replyMessage($event->getReplyToken(), Question::askWhyYouAreInGoodCondition($question, $user));
                        }
                    } else if ($question->order_number === 3 || $question->order_number === 4) {
                        Diary::create([
                            'user_uuid' => $user->uuid,
                            'condition_id' => $question->condition_id,
                            'detail' => $event->getText()
                        ]);
                        $this->bot->replyMessage($event->getReplyToken(), Question::thanksMessage($question));
                    }
                }

                if ($event->getText() === '仕事を早く終わらせることができた！') {
                    $quick_reply_buttons = [
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('できた', 'できた')),
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('できなかった', 'できなかった'))
                    ];
                    $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
                    $text_message_builder = new TextMessageBuilder('今日したかった「朝6:00に起きる」はできた?', $quick_reply_message_builder);
                    $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                    $multi_message->add(new TextMessageBuilder('それはよかった！'));
                    $multi_message->add($text_message_builder);
                    $test = $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                    Log::debug((array)$test);
                }
                if ($event->getText() === 'できなかった') {
                    $quick_reply_buttons = [
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ある', 'ある')),
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ない', 'ない'))
                    ];
                    $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
                    $text_message_builder = new TextMessageBuilder('明日したいことや改善したいことはある？', $quick_reply_message_builder);
                    $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                    $multi_message->add(new TextMessageBuilder('それは残念！また頑張ろう！'));
                    $multi_message->add($text_message_builder);
                    $test = $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                    Log::debug((array)$test);
                }
                if ($event->getText() === 'ある') {
                    $text_message_builder = new TextMessageBuilder('ちなみにどんなことか教えて！');
                    $test = $this->bot->replyMessage($event->getReplyToken(), $text_message_builder);
                    Log::debug((array)$test);
                }
                if ($event->getText() === '朝6:00に起きる') {
                    $quick_reply_buttons = [
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ある', 'ある')),
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ない', 'ない'))
                    ];
                    $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
                    $text_message_builder = new TextMessageBuilder('他にも明日したいことや改善したいことはある？', $quick_reply_message_builder);
                    $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                    $multi_message->add(new TextMessageBuilder('それじゃあ明日「' . $event->getText() . '」ができるように頑張ろう！'));
                    $multi_message->add($text_message_builder);
                    $test = $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                    Log::debug((array)$test);
                }
                if ($event->getText() === 'ない') {
                    $builder =
                        new TemplateMessageBuilder(
                            '今日も一日お疲れ様です！明日も頑張っていきましょう！', // チャット一覧に表示される
                            new ButtonTemplateBuilder(
                                null, // title
                                '今日も一日お疲れ様です！明日も頑張っていきましょう！', // text
                                null, // 画像url
                                [
                                    new UriTemplateActionBuilder('もっと記録する', 'https://liff.line.me/1657690379-MG15W7yl')
                                ]
                            )
                        );
                    $test = $this->bot->replyMessage($event->getReplyToken(), $builder);
                    Log::debug((array)$test);
                }
            } else if ($event->getType() === 'postback') {
                if ($event->getPostbackData() === '絶好調') {
                    $this->bot->replyText($event->getReplyToken(), 'どんなところが絶好調だったか教えて！');
                }
            }
        }

        Log::debug($status_code);

        return response('', $status_code, []);
    }
}
