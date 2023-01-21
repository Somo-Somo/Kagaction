<?php

namespace App\UseCases\Line;

use App\Models\Condition;
use App\Models\Diary;
use App\Models\Feeling;
use App\Models\User;
use App\Services\CarouselContainerBuilder\PageTransitionBtnCarouselContainerBuilder;
use App\Services\CarouselContainerBuilder\TalkLogCarouselContainerBuilder;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class WatchLogAction
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
     * @param string $view_week
     * @param User $user
     * @param Carbon $today
     * @param int $current_page
     * @return
     */
    public function invoke($view_week, $user, $today, $current_page)
    {
        $view_week_en = $view_week === '今週' ? 'THIS_WEEK' : 'LAST_WEEK';
        $unview_week = $view_week === '今週' ? '先週' : '今週';
        $quick_reply_button_message = new QuickReplyMessageBuilder([
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('📝 ' . $unview_week . 'の記録', $unview_week . 'の記録')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('📊 週間レポート',  '週間レポート')),
        ]);

        if ($view_week === '今週') {
            $start_day = $today->startOfWeek()->toDateString();
            $end_day = $today->endOfWeek()->toDateString();
        } else if ($view_week === '先週') {
            $start_day = $today->subWeek()->startOfWeek()->toDateString();
            $end_day = $today->subWeek()->endOfWeek()->toDateString();
        }
        $conditions = Condition::where('user_uuid', $user->uuid)->whereDate('date', '>=', $start_day)->whereDate('date', '<=', $end_day)->get();
        $talk_log_carousel_columns = [];
        Log::debug((array)$conditions);
        foreach ($conditions as $condition) {
            $feeling = Feeling::where('condition_id', $condition->id)->first();
            $diary = Diary::where('condition_id', $condition->id)->first();
            $talk_log_carousel_columns[] = TalkLogCarouselContainerBuilder::createTalkLogBubbleContainer(
                $condition,
                $feeling,
                $diary
            );
        }

        $talk_log_num = count($talk_log_carousel_columns);
        $multi_message = new MultiMessageBuilder();
        if ($talk_log_num > 0) {
            if ($talk_log_num > 10) {
                $talk_log_carousel_limit = 10;
                $last_page = intval(ceil($talk_log_num / $talk_log_carousel_limit));
                $slice_start = $current_page === 1 ? 0 : $talk_log_carousel_limit + (($current_page - 2) * 10);
                $talk_log_carousel_columns = array_slice($talk_log_carousel_columns, $slice_start, $talk_log_carousel_limit);

                if ($current_page !== 1) {
                    array_unshift(
                        $talk_log_carousel_columns,
                        PageTransitionBtnCarouselContainerBuilder::createPageTransitionBtnBubbleContainer(
                            $current_page,
                            $talk_log_num,
                            $talk_log_carousel_limit,
                            $type = 'prev',
                            $action_value = $view_week_en . '_TALK_LOG_PAGE_TRANSITION'
                        )
                    );
                }
                if ($current_page !== $last_page) {
                    $talk_log_carousel_columns[] =
                        PageTransitionBtnCarouselContainerBuilder::createPageTransitionBtnBubbleContainer(
                            $current_page,
                            $talk_log_num,
                            $talk_log_carousel_limit,
                            $type = 'next',
                            $action_value = $view_week_en . '_TALK_LOG_PAGE_TRANSITION'
                        );
                }
            }
            $talk_log_carousels = new CarouselContainerBuilder($talk_log_carousel_columns);
            if ($current_page === 1) {
                $multi_message->add(new TextMessageBuilder('📝 ' . $view_week . 'の記録'));
            }
            $multi_message->add(new FlexMessageBuilder(
                '📝 ' . $view_week . 'の記録',
                $talk_log_carousels,
                $quick_reply_button_message
            ));
        } else {
            $multi_message->add(new TextMessageBuilder(
                'お探ししたところ' . $view_week . 'の記録はありませんでした。',
                $quick_reply_button_message
            ));
        }
        return $multi_message;
    }
}
