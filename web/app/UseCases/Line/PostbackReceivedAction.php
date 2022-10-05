<?php

namespace App\UseCases\Line;

use App\Models\CheckedTodo;
use App\Models\User;
use App\Models\Todo;
use App\Models\LineUsersQuestion;
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
     * @param App\UseCases\Line\DateResponseAction
     */
    protected $date_response_action;

    /**
     * @param App\UseCases\Line\Todo\SelectTodoListAction
     */
    protected $select_todo_list_action;

    /**
     * @param \App\UseCases\Line\Todo\RenameTodo
     */
    protected $rename_todo;

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
     * @param App\UseCases\Line\DateResponseAction $date_response_action
     * @param App\UseCases\Line\Todo\SelectTodoListAction $select_todo_list_action
     * @param \App\UseCases\Line\Todo\RenameTodo $rename_todo
     * @param \App\UseCases\Line\Todo\DeleteTodo $delete_todo
     * @param \App\UseCases\Line\Todo\CheckTodo $check_todo
     * @param \App\UseCases\Line\Todo\ChangeDate $change_date
     */
    public function __construct(
        DateResponseAction $date_response_action,
        \App\UseCases\Line\Todo\SelectTodoListAction $select_todo_list_action,
        \App\UseCases\Line\Todo\RenameTodo $rename_todo,
        \App\UseCases\Line\Todo\DeleteTodo $delete_todo,
        \App\UseCases\Line\Todo\CheckTodo $check_todo,
        \App\UseCases\Line\Todo\ChangeDate $change_date,
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->date_response_action = $date_response_action;
        $this->select_todo_list_action = $select_todo_list_action;
        $this->rename_todo = $rename_todo;
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
        [$action_key, $action_value] = explode("=", $action_data);
        [$uuid_key, $uuid_value] = explode("=", $uuid_data);

        if (isset(LineUsersQuestion::TODO_LIST[$action_value])) {
            $this->select_todo_list_action->invoke($event, $line_user, $action_value);
        } else if ($action_value === 'ADD_TODO') {
            if ($uuid_value) {
                $parent_todo = Todo::where('uuid', $uuid_value)->first();
                // 返信メッセージ
                $this->bot->replyText($event->getReplyToken(), Todo::askTodoName($parent_todo));
                //質問の更新
                $line_user->question->update([
                    'question_number' => LineUsersQuestion::TODO,
                    'parent_uuid' => $uuid_value,
                ]);
            }
        } else if ($action_value === 'LIMIT_DATE') {
            // 日付に関する質問の場合
            $this->date_response_action->invoke($event, $line_user, $uuid_value);
        } else if ($action_value === 'CHANGE_TODO') {
            $builder = Todo::changeTodo(Todo::where('uuid', $uuid_value)->first());
            $this->bot->replyMessage($event->getReplyToken(), $builder);
        } else if ($action_value === 'RENAME_TODO') {
            $this->rename_todo->invoke($event, $line_user, $uuid_value);
        } else if (isset(LineUsersQuestion::DELETE_TODO[$action_value])) {
            $this->delete_todo->invoke($event, $line_user, $action_value, $uuid_value);
        } else if (isset(LineUsersQuestion::CHANGE_DATE[$action_value])) {
            $this->change_date->invoke($event, $line_user, $action_value, $uuid_value);
        } else if (isset(CheckedTodo::CHECK_TODO[$action_value])) {
            $this->check_todo->invoke($event, $line_user, $action_value, $uuid_value);
        }
        return;
    }
}
