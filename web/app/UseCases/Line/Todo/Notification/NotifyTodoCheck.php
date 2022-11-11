<?php

namespace App\UseCases\Line\Todo\Notification;

use App\Models\CheckedTodo;
use App\Models\LineUsersQuestion;
use App\Models\Todo;
use App\Models\TodoCheckNotificationDateTime;
use App\UseCases\Line\Todo\CreateTodoListCarouselColumns;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Illuminate\Support\Facades\Log;
use DateTime;


class NotifyTodoCheck
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
     * @param App\UseCases\Line\Todo\CreateTodoListCarouselColumns
     */
    protected $create_todo_list_carousel_columns;

    /**
     */
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->create_todo_list_carousel_columns = new CreateTodoListCarouselColumns();
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
        $day_of_week = date('w');
        $recive_notification_users = TodoCheckNotificationDateTime::where('notification_time', $time)
            ->where(function ($query) use ($day_of_week) {
                $query->orwhere('notification_date', 7)
                    ->orwhere('notification_date', $day_of_week);
            })->get();
        if (count($recive_notification_users) > 0) {
            Log::info('has');
            foreach ($recive_notification_users as  $recive_notification_user) {
                $today_date_time = new DateTime();
                $today = $today_date_time->format('Y-m-d');
                $todo_list = Todo::where('user_uuid', $recive_notification_user->user_uuid)
                    ->where('date', '<=', $today)
                    ->orderBy('date', 'desc')
                    ->get();
                if (count($todo_list) > 0) {
                    $notify_todo_check_message =
                        '振り返りの時間です。' . "\n" . $recive_notification_user->users->name . 'さんが今日までに遂げるもの一覧です。' . "\n" . '頑張って振り返っていきましょう!';
                    $action_type = 'CHECK_TODO_BY_TODAY';
                    $second_message = $this->create_todo_list_carousel_columns->invoke(
                        $recive_notification_user->users,
                        $todo_list,
                        $action_type,
                        $current_page = 1
                    );
                    LineUsersQuestion::where('line_user_id', $recive_notification_user->users->line_user_id)->update([
                        'checked_todo' => CheckedTodo::CHECK_TODO[$action_type]
                    ]);
                } else {
                    $notify_todo_check_message =
                        '振り返りの時間です。' . "\n" . $recive_notification_user->users->name . 'さんが今日までに遂げることは0件です。' . "\n" . '目標達成のために遂げることを追加していきましょう！';
                    $second_message = $this->create_todo_list_carousel_columns->invoke(
                        $recive_notification_user->users,
                        Todo::where('user_uuid', $recive_notification_user->users->uuid)->get(),
                        'SHOW_TODO_LIST_TO_ADD_TODO', // $action_type
                        $current_page = 1
                    );
                }

                $multi_message_builder = new MultiMessageBuilder();
                $multi_message_builder->add(new TextMessageBuilder($notify_todo_check_message));
                $multi_message_builder->add($second_message);
                $this->bot->pushMessage(
                    $recive_notification_user->users->line_user_id,
                    $multi_message_builder
                );
            }
        }
        return;
    }
}
