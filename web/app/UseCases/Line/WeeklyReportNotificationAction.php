<?php

namespace App\UseCases\Line;

use App\Models\Condition;
use App\Models\Feeling;
use App\Models\ImageReport;
use App\Models\WeeklyReportNotification;
use Carbon\Carbon;
use DateTime;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class WeeklyReportNotificationAction
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
        $start_day = $today->copy()->subWeek()->startOfWeek()->toDateString();
        $end_day = $today->copy()->subWeek()->endOfWeek()->toDateString();
        $datetime = new DateTime();
        $time = $datetime->format('H') . ':00:00';
        $recive_notifications = WeeklyReportNotification::where('time', '09:00:00')->get();
        if (count($recive_notifications) > 0) {
            Log::info('has');
            foreach ($recive_notifications as  $recive_notification) {
                $user = $recive_notification->user;
                $image_url = ImageReport::getWeeklyImageReportUrl(
                    $user->uuid,
                    $start_day,
                    $end_day
                );
                if ($image_url) {
                    $last_week_conditions = Condition::where('user_uuid', $user->uuid)
                        ->whereDate('date', '>=', $start_day)
                        ->whereDate('date', '<', $end_day)
                        ->select('evaluation')
                        ->selectRaw('COUNT(evaluation) as count_evaluation')
                        ->groupBy('evaluation')
                        ->orderBy('count_evaluation', 'desc')
                        ->get();
                    $count_last_week_conditions = count(Condition::where('user_uuid', $user->uuid)
                        ->whereDate('date', '>=', $start_day)
                        ->whereDate('date', '<', $end_day)
                        ->get());

                    $last_week_feelings = Feeling::where('user_uuid', $user->uuid)
                        ->whereDate('date', '>=', $start_day)
                        ->whereDate('date', '<', $end_day)
                        ->select('feeling_type')
                        ->selectRaw('COUNT(feeling_type) as count_feeling_type')
                        ->groupBy('feeling_type')
                        ->orderby('count_feeling_type', 'desc')
                        ->get();
                    $count_last_week_feelings = count(Feeling::where('user_uuid', $user->uuid)
                        ->whereDate('date', '>=', $start_day)
                        ->whereDate('date', '<', $end_day)
                        ->get());
                    if (count($last_week_conditions) > 0) {
                        $condition_text = '調子: ' . "\n";
                        foreach ($last_week_conditions as $key => $value) {
                            if ($key === 0) {
                                $rank = '1st:  ';
                            } else if ($key === 1) {
                                $rank = '2nd: ';
                            } else if ($key === 2) {
                                $rank = '3rd: ';
                            } else if ($key === 3) {
                                $rank = '4th: ';
                            } else if ($key === 4) {
                                $rank = '5th: ';
                            }
                            $condition_text .= $rank . Condition::CONDITION_EMOJI[$value->evaluation] .
                                ' ' . round(intval($value->count_evaluation) / $count_last_week_conditions * 100) .
                                '%' . "\n";
                        }
                        if (count($last_week_feelings) > 0) {
                            $feeling_text =  '気持ち:';
                            foreach ($last_week_feelings as $key => $value) {
                                if ($key < 5) {
                                    if ($key === 0) {
                                        $rank = '1st: ';
                                    } else if ($key === 1) {
                                        $rank = '2nd: ';
                                    } else if ($key === 2) {
                                        $rank = '3rd: ';
                                    } else if ($key === 3) {
                                        $rank = '4th: ';
                                    } else if ($key === 4) {
                                        $rank = '5th: ';
                                    }
                                    $feeling_text .= "\n" . $rank . Feeling::FEELING_EMOJI[$value->feeling_type]
                                        . ' ' . round(intval($value->count_feeling_type) / $count_last_week_feelings * 100)
                                        . '%';
                                }
                            }
                        }
                        $text = $condition_text . "\n" . $feeling_text;
                        $multi_message = new MultiMessageBuilder();
                        $multi_message->add(new TextMessageBuilder($start_day . ' ~ ' . $end_day . 'までの週間レポート'));
                        $multi_message->add(new TextMessageBuilder($text));
                        $multi_message->add(new ImageMessageBuilder($image_url, $image_url));
                        $this->bot->pushMessage('U49669ef1e05a33be44e450d020d23504', $multi_message);
                    }
                }
            }
        }
        return;
    }
}
