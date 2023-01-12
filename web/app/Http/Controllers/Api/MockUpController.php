<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Question;
use App\Models\Condition;
use App\Http\Controllers\Controller;
use App\Models\Diary;
use App\Models\Feeling;
use App\Models\ImageReport;
use App\Models\SelfCheckNotification;
use App\UseCases\Line\FollowAction;
use App\Services\LineBotService;
use App\Services\CarouselContainerBuilder\OtherMenuCarouselContainerBuilder;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
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
    public function reply(Request $request, FollowAction $follow_action)
    {
        $status_code = $this->line_bot_service->eventHandler($request);

        // リクエストをEventオブジェクトに変換する
        $events = $this->bot->parseEventRequest($request->getContent(), $request->header('x-line-signature'));

        foreach ($events as $event) {
            $user = User::where('line_id', $event->getUserId())->first();
            $question = Question::where('line_user_id', $event->getUserId())->first();
            if ($event->getType() === 'follow') {
                $follow_action->invoke($event->getUserId());
            } elseif ($event->getType() === 'message') {
                if ($event->getText() === '話す') {
                    $user_name = $this->bot->getProfile($event->getUserId())->getJSONDecodedBody()['displayName'];
                    $question->update([
                        'condition_id' => null,
                        'feeling_id' => null,
                        'operation_type' => 1,
                        'order_number' => 1
                    ]);
                    $this->bot->replyMessage(
                        $event->getReplyToken(),
                        Condition::askCondition($user_name)
                    );
                    return;
                } else if ($event->getText() === '記録をみる') {
                    $user = User::where('line_id', $event->getUserId())->first();
                    $imageReport = ImageReport::setWeeklyImageReport($user->uuid);
                    $this->bot->replyMessage($event->getReplyToken(), $imageReport);
                    return response('', $status_code, []);
                } else if ($event->getText() === '設定') {
                    $this->bot->replyMessage(
                        $event->getReplyToken(),
                        new FlexMessageBuilder('メニュー: その他', OtherMenuCarouselContainerBuilder::createCarouselContainerBuilder())
                    );
                    $question->update([
                        'operation_type' => null,
                        'order_number' => null,
                    ]);
                }
                if ($question->operation_type === 1) {
                    if ($question->order_number === 1) {
                        $date_time = new DateTime();
                        // 保存
                        $condition = Condition::create([
                            'user_uuid' => $user->uuid,
                            'evaluation' => Condition::EVALUATION[$event->getText()],
                            'date' => $date_time->format('Y-m-d'),
                            'time' => $date_time->format('H:i:s')
                        ]);
                        $question->update(['condition_id' => $condition->id, 'order_number' => 2]);
                        if ($event->getText() === '絶好調' || $event->getText() === '好調') {
                            $this->bot->replyMessage($event->getReplyToken(), Question::askWhatIsHappened($user, $event->getText()));
                        } elseif ($event->getText() === 'まあまあ') {
                            $this->bot->replyMessage($event->getReplyToken(), Question::pleaseWriteWhatHappened($question, $user));
                        } elseif ($event->getText() === '不調' || $event->getText() === '絶不調') {
                            $this->bot->replyMessage($event->getReplyToken(), Question::askAboutFeeling($question));
                        }
                    } elseif ($question->order_number === 2) {
                        if ($question->condition->evaluation > 3) {
                            if ($event->getText() === 'ある') {
                                $this->bot->replyMessage($event->getReplyToken(), Question::pleaseWriteWhatHappened($question, $user));
                            } elseif ($event->getText() === 'ない') {
                                $this->bot->replyMessage($event->getReplyToken(), Question::askWhyYouAreInGoodCondition($question, $user));
                            }
                        } elseif ($question->condition->evaluation < 3) {
                            $condition = Condition::where('id', $question->condition_id)->first();
                            $feeling = Feeling::create([
                                'user_uuid' => $user->uuid,
                                'condition_id' => $condition->id,
                                'feeling_type' => Feeling::JA_EN[$event->getText()],
                                'date' => $condition->date,
                                'time' => $condition->time
                            ]);

                            $question->update(['order_number' => 3, 'feeling_id' => $feeling->id]);
                            $this->bot->replyMessage($event->getReplyToken(), Question::questionAfterAskAboutFeeling($question, $user, $feeling));
                        }
                    } elseif ($question->order_number === 3 || $question->order_number === 4) {
                        if ($question->condition->evaluation > 2) {
                            Diary::create([
                                'user_uuid' => $user->uuid,
                                'condition_id' => $question->condition_id,
                                'detail' => $event->getText()
                            ]);
                            $this->bot->replyMessage($event->getReplyToken(), Question::thanksMessage($question, $event->getText()));
                        } elseif ($question->condition->evaluation < 3) {
                            $this->bot->replyMessage($event->getReplyToken(), Question::thanksMessage($question, $event->getText()));
                        }
                    }
                } else if ($question->operation_type === 3) {
                    $self_check_notification = SelfCheckNotification::where('user_uuid', $user->uuid)->orderBy('time')->get();
                    Log::debug((array)$self_check_notification);
                    if ($question->order_number === 1) {
                        if ($event->getText() === 'セルフチェックの通知の変更') {
                            $quick_reply_message_builder = [];
                            if (count($self_check_notification) < 3) {
                                $quick_reply_message_builder[] =   new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🔔通知の追加', '通知の追加'));
                            }
                            if (count($self_check_notification) > 0) {
                                $quick_reply_message_builder[] = new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('⏰時間の変更', '時間の変更'));
                                $quick_reply_message_builder[] =  new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🔕通知の停止', '通知の停止'));
                            }
                            $this->bot->replyMessage(
                                $event->getReplyToken(),
                                new TextMessageBuilder(
                                    'こちらから選択してください！',
                                    new QuickReplyMessageBuilder($quick_reply_message_builder)
                                )
                            );
                            $question->update(['order_number' => 2]);
                        } else if ($event->getText() === '週間レポートの通知の変更') {
                        }
                    } else if ($question->order_number === 2) {
                        if ($event->getText() === '通知の追加') {
                            $flex_message = SelfCheckNotification::selectDateTimeFlexMessageBuilder(
                                [
                                    SelfCheckNotification::createSettingTimeMessageBuilder('AM'),
                                    SelfCheckNotification::createSettingTimeMessageBuilder('PM'),
                                ]
                            );
                            $multi_message = new MultiMessageBuilder();
                            $multi_message->add(new TextMessageBuilder('時間を選択してください！'));
                            $multi_message->add($flex_message);
                            $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                        } else if ($event->getText() === '通知の停止' || $event->getText() === '時間の変更') {
                            $text_message = $event->getText() === '時間の変更' ? '変更する通知の時間を選択してください' : '停止する通知の時間を選択してください';
                            $quick_reply_message_builder = [];
                            foreach ($self_check_notification as $key => $value) {
                                $quick_reply_message_builder[] = new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('⏰' . substr($value->time, 0, -3), substr($value->time, 0, -3)));
                            }
                            $this->bot->replyMessage(
                                $event->getReplyToken(),
                                new TextMessageBuilder($text_message, new QuickReplyMessageBuilder($quick_reply_message_builder))
                            );
                            $order_number =  $event->getText() === '時間の変更' ? 3 : 4;
                            $question->update(['order_number' => $order_number]);
                        }
                    } else if ($question->order_number === 3) {
                        # 時間の変更
                        $flex_message = SelfCheckNotification::selectDateTimeFlexMessageBuilder(
                            [
                                SelfCheckNotification::createSettingTimeMessageBuilder('AM'),
                                SelfCheckNotification::createSettingTimeMessageBuilder('PM'),
                            ]
                        );
                        $multi_message = new MultiMessageBuilder();
                        $multi_message->add(new TextMessageBuilder('新しい時間を選択してください！'));
                        $multi_message->add($flex_message);
                        $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                    } elseif ($question->order_number === 4) {
                        # 通知の停止
                        $quick_reply_message_builder = [];
                        $quick_reply_message_builder[] =  new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🔔通知の追加', '通知の追加'));
                        if (count($self_check_notification) > 1) {
                            $quick_reply_message_builder[] = new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('⏰時間の変更', '時間の変更'));
                            $quick_reply_message_builder[] =  new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🔕通知の停止', '通知の停止'));
                        }
                        SelfCheckNotification::where('user_uuid', $user->uuid)->where('time', $event->getText() . ':00')->delete();
                        $this->bot->replyMessage(
                            $event->getReplyToken(),
                            new TextMessageBuilder(
                                '毎日' . $event->getText() . 'の通知を停止しました！',
                                new QuickReplyMessageBuilder($quick_reply_message_builder)
                            )
                        );
                        $question->update(['order_number' => 1]);
                    }
                }
            } else if ($event->getType() === 'postback') {
                //postbackのデータをactionとuuidで分割
                list($action_data, $uuid_data) = explode("&", $event->getPostbackData());
                [$action_key, $action_type] = explode("=", $action_data);
                [$select_key, $select_value] = explode("=", $uuid_data);
                if ($action_type === 'SETTING_UP_NOTIFICATION') {
                    $self_check_notification = SelfCheckNotification::where('user_uuid', $user->uuid)->orderBy('time')->get();
                    if (count($self_check_notification) > 0) {
                        $self_check_text =  'セルフチェック';
                        foreach ($self_check_notification as $key => $value) {
                            $self_check_text .= "\n" . "・" . substr($value->time, 0, -3);
                        }
                    } else {
                        $self_check_text = 'セルフチェック: 末設定';
                    }
                    $quick_reply_message_builder = new QuickReplyMessageBuilder(
                        [
                            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('❤セルフチェックの通知の変更', 'セルフチェックの通知の変更')),
                            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('📊週間レポートの通知の変更', '週間レポートの通知の変更')),
                        ]
                    );
                    $multi_message = new MultiMessageBuilder();
                    $multi_message->add(new TextMessageBuilder('通知設定'));
                    $multi_message->add(new TextMessageBuilder($self_check_text));
                    $multi_message->add(new TextMessageBuilder('週間レポート' . "\n" . '・未設定', $quick_reply_message_builder));
                    $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                    $question->update([
                        'condition_id' => null,
                        'feeling_id' => null,
                        'operation_type' => 3, // 通知の設定
                        'order_number' => 1,
                    ]);
                } else if ($action_type === 'SELF_CHECK_NOTIFICATION_TIME') {
                    SelfCheckNotification::updateOrCreate(
                        ['user_uuid' => $user->uuid, 'time' => $select_value . ':00'],
                    );
                    $message = $question->order_number === 2 ? 'に設定しました' : 'に変更しました';
                    $this->bot->replyMessage(
                        $event->getReplyToken(),
                        new TextMessageBuilder('セルフチェックの通知を毎日:' . $select_value . $message)
                    );
                    $question->update([
                        'operation_type' => null, // 通知の設定
                        'order_number' => null,
                    ]);
                }
            }
        }

        Log::debug($status_code);

        return response('', $status_code, []);
    }

    public function debug()
    {
        // return view('weekly_report', compact('data'));
        $data = 'test';
        Log::debug('abc');
        return view('index', compact('data'));
    }
}
