<?php

namespace App\UseCases\Line\Todo\Notification;

use App\Models\TodoCheckNotificationDateTime;
use App\Models\User;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use DateTime;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class SetupNotificationForTodoCheck
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
     * Todoの名前を変更する
     *
     * @param object $event
     * @param User $line_user
     * @return
     */
    public function invoke(
        object $event,
        User $line_user,
        string $action_type,
        string $notify_setting_value
    ) {
        if ($action_type === 'IF_YOU_WANT_TO_SET_UP_NOTIFY_CHECK_TODO') {
            $template_message_builder = TodoCheckNotificationDateTime::createConfirmDoYouWantToSetUpBuilder($line_user);
            $this->bot->replyMessage(
                $event->getReplyToken(),
                $template_message_builder
            );
        } else if ($action_type === 'STOP_SETTING_NOTIFICATION_CHECK_TODO') {
            if (!$notify_setting_value) {
                $this->bot->replyMessage(
                    $event->getReplyToken(),
                    TodoCheckNotificationDateTime::createConfirmYouStopNotificationBuilder()
                );
            } else if ($notify_setting_value === 'STOP') {
                TodoCheckNotificationDateTime::where('user_uuid', $line_user->uuid)->delete();
                // 通知のSTOP
                $this->bot->replyMessage(
                    $event->getReplyToken(),
                    new TextMessageBuilder('振り返りの時間の通知を停止しました。')
                );
            } else if ($notify_setting_value === 'CANCEL') {
                // キャンセル
                $this->bot->replyMessage(
                    $event->getReplyToken(),
                    new TextMessageBuilder('振り返りの時間の通知を停止キャンセルしました。')
                );
            }
        } else if ($action_type === 'SETTING_NOTIFY_DAY_OF_WEEK') {
            $setting_day_of_week_message_builder = TodoCheckNotificationDateTime::createSettingDayOfWeekMessageBuilder();
            $this->bot->replyMessage(
                $event->getReplyToken(),
                $setting_day_of_week_message_builder
            );
        } else if ($action_type === 'SETTING_NOTIFY_MERIDIEM') {
            $setting_meridiem_builder = TodoCheckNotificationDateTime::selectMeridiemCarouselMessageBuilder($notify_setting_value);
            $this->bot->replyMessage(
                $event->getReplyToken(),
                $setting_meridiem_builder
            );
        } else if ($action_type === 'SETTING_NOTIFY_DATETIME') {
            $setting_time_message_builder = TodoCheckNotificationDateTime::createSettingTimeMessageBuilder($notify_setting_value);
            $this->bot->replyMessage(
                $event->getReplyToken(),
                $setting_time_message_builder
            );
        } else if ($action_type === 'FINISH_SETTING_NOTIFICATION_CHECK_TODO') {
            $day_of_weeks = ['日', '月', '火', '水', '木', '金', '土', '毎日'];
            [$day_of_week, $time] = explode(",", $notify_setting_value);
            $notification_date = $day_of_weeks[$day_of_week] === '毎日' ?
                $day_of_weeks[$day_of_week] : '毎週' . $day_of_weeks[$day_of_week] . '曜日';
            $reply_message = '振り返りの時間を「' . $notification_date  . $time . '」に設定しました！';
            $this->bot->replyMessage(
                $event->getReplyToken(),
                new TextMessageBuilder($reply_message)
            );
            TodoCheckNotificationDateTime::updateOrCreate(
                ['user_uuid' => $line_user->uuid],
                [
                    'notification_date' => (int)$day_of_week,
                    'notification_time' => $time
                ]
            );
        }
        return;
    }
}
