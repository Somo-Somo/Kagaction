<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Question;
use App\Models\Condition;
use App\Http\Controllers\Controller;
use App\Models\Diary;
use App\Models\Feeling;
use App\Models\ImageReport;
use App\UseCases\Line\FollowAction;
use App\Services\LineBotService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
    public function reply(Request $request, FollowAction $follow_action)
    {
        $status_code = $this->line_bot_service->eventHandler($request);

        // リクエストをEventオブジェクトに変換する
        $events = $this->bot->parseEventRequest($request->getContent(), $request->header('x-line-signature'));

        foreach ($events as $event) {
            if ($event->getType() === 'follow') {
                $follow_action->invoke($event->getUserId());
            } elseif ($event->getType() === 'message') {
                $user = User::where('line_id', $event->getUserId())->first();
                $question = Question::where('line_user_id', $event->getUserId())->first();
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
                    // $today = Carbon::today();
                    // $eightDays = Carbon::today()->subDay(8);
                    // $condition = Condition::select(DB::raw('evaluation, COUNT(evaluation) AS count_evaluation'))
                    //     ->where('user_uuid', $user->uuid)
                    //     ->whereDate('date', '>=', $eightDays)
                    //     ->whereDate('date', '<', $today)
                    //     ->groupBy('evaluation')
                    //     ->orderBy('evaluation', "desc")
                    //     ->get();
                    // $sortCondition = [['num' => 0], ['num' => 0], ['num' => 0], ['num' => 0], ['num' => 0]];
                    // foreach ($condition as $value) {
                    //     $key = abs(intval($value->evaluation) - 5);
                    //     $sortCondition[$key] = ['num' => intval($value->count_evaluation)];
                    // }
                    // $feeling =  Feeling::select(DB::raw('feeling_type, COUNT(feeling_type) AS count_feeling_type'))
                    //     ->where('user_uuid', $user->uuid)
                    //     ->groupBy('feeling_type')
                    //     ->whereDate('date', '>=', $eightDays)
                    //     ->whereDate('date', '<', $today)
                    //     ->orderBy('count_feeling_type', 'desc')
                    //     ->get();
                    // $sortFeeling = [];
                    // foreach ($feeling as $value) {
                    //     $sortFeeling[] = [
                    //         'name' => $value->feeling_type,
                    //         'num' => intval($value->count_feeling_type)
                    //     ];
                    // }
                    $imageReport = ImageReport::setWeeklyImageReport();
                    $this->bot->replyMessage($event->getReplyToken(), $imageReport);
                    return response('', $status_code, []);
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
