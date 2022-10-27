<?php

namespace App\UseCases\Line;

use App\Models\CheckedTodo;
use App\Models\Habit;
use App\Models\User;
use App\Models\Todo;
use App\Models\LineUsersQuestion;
use App\Models\TodoCheckNotificationDateTime;
use App\UseCases\Line\Todo\Notification\SetupNotificationForTodoCheck;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;

class PostbackReceivedAction
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
     * @param \App\UseCases\Line\Todo\DateResponseAction
     */
    protected $date_response_action;

    /**
     * @param \App\UseCases\Line\Todo\HabitSettingResponseAction
     */
    protected $habit_setting_response_action;

    /**
     * @param App\UseCases\Line\Todo\SelectTodoListAction
     */
    protected $select_todo_list_action;

    /**
     * @param \App\UseCases\Line\Todo\RenameTodo
     */
    protected $rename_todo;

    /**
     * @param \App\UseCases\Line\Todo\AddTodo
     */
    protected $add_todo;

    /**
     * @param \App\UseCases\Line\Todo\DeleteTodo
     */
    protected $delete_todo;

    /**
     * @param \App\UseCases\Line\Todo\CheckTodo
     */
    protected $check_todo;

    /**
     * @param \App\UseCases\Line\Todo\ChangeDate
     */
    protected $change_date;

    /**
     * @param \App\UseCases\Line\Todo\DateResponseAction $date_response_action
     * @param \App\UseCases\Line\Todo\HabitSettingResponseAction $date_response_action
     * @param App\UseCases\Line\Todo\SelectTodoListAction $select_todo_list_action
     * @param \App\UseCases\Line\Todo\RenameTodo $rename_todo
     * @param \App\UseCases\Line\Todo\AddTodo $add_todo
     * @param \App\UseCases\Line\Todo\DeleteTodo $delete_todo
     * @param \App\UseCases\Line\Todo\CheckTodo $check_todo
     * @param \App\UseCases\Line\Todo\ChangeDate $change_date
     */
    public function __construct(
        \App\UseCases\Line\Todo\DateResponseAction $date_response_action,
        \App\UseCases\Line\Todo\HabitSettingResponseAction $habit_setting_response_action,
        \App\UseCases\Line\Todo\SelectTodoListAction $select_todo_list_action,
        \App\UseCases\Line\Todo\RenameTodo $rename_todo,
        \App\UseCases\Line\Todo\AddTodo $add_todo,
        \App\UseCases\Line\Todo\DeleteTodo $delete_todo,
        \App\UseCases\Line\Todo\CheckTodo $check_todo,
        \App\UseCases\Line\Todo\ChangeDate $change_date,
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->date_response_action = $date_response_action;
        $this->habit_setting_response_action = $habit_setting_response_action;
        $this->select_todo_list_action = $select_todo_list_action;
        $this->rename_todo = $rename_todo;
        $this->add_todo = $add_todo;
        $this->delete_todo = $delete_todo;
        $this->check_todo = $check_todo;
        $this->change_date = $change_date;
    }

    /**
     * 受け取ったメッセージを場合分けしていく
     *
     * @param object $event
     * @return
     */
    public function invoke(object $event)
    {
        // 該当のユーザーを探す
        $line_user = User::where('line_user_id', $event->getUserId())->first();

        //postbackのデータをactionとuuidで分割
        list($action_data, $uuid_data) = explode("&", $event->getPostbackData());
        [$action_key, $action_type] = explode("=", $action_data);
        [$second_key, $second_value] = explode("=", $uuid_data);

        if (isset(LineUsersQuestion::TODO_LIST[$action_type])) {
            $this->select_todo_list_action->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(Todo::ADD_TODO[$action_type])) {
            $this->add_todo->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(Todo::DATE[$action_type])) {
            // 日付に関する質問の場合
            $this->date_response_action->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(Habit::HABIT[$action_type])) {
            $this->habit_setting_response_action->invoke($event, $line_user, $action_type, $second_value);
        } else if ($action_type === 'CHANGE_TODO') {
            $builder = Todo::changeTodo(Todo::where('uuid', $second_value)->first());
            $this->bot->replyMessage($event->getReplyToken(), $builder);
        } else if ($action_type === 'RENAME_TODO') {
            $this->rename_todo->invoke($event, $line_user, $second_value);
        } else if (isset(Todo::DELETE_TODO[$action_type])) {
            $this->delete_todo->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(Todo::CHANGE_DATE[$action_type])) {
            $this->change_date->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(CheckedTodo::CHECK_TODO[$action_type])) {
            $this->check_todo->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(TodoCheckNotificationDateTime::SETTING_NOTIFICATION_FOR_TODO_CHECK[$action_type])) {
            $notify_check_todo = new SetupNotificationForTodoCheck();
            $notify_check_todo->invoke($event, $line_user, $action_type, $second_value);
        } else if (isset(TodoCheckNotificationDateTime::NOTIFY_TODO_CHECK[$action_type])) {
            # code...
        }
        return;
    }
}
