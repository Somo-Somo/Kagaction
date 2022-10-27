<?php

namespace App\UseCases\Line;

use App\Models\CheckedTodo;
use App\Models\User;
use App\Models\Todo;
use App\Models\LineUsersQuestion;
use App\UseCases\Line\ProjectResponseAction;
use App\UseCases\Line\Todo\Notification\NotifyTodoCheck;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;


class MessageReceivedAction
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
     * @param App\UseCases\Line\ProjectResponseAction
     */
    protected $project_response_action;

    /**
     * @param App\UseCases\Line\TodoResponseAction
     */
    protected $todo_response_action;

    /**
     * @param \App\UseCases\Line\Todo\RenameTodo
     */
    protected $rename_todo;

    /**
     * @param App\UseCases\LINE\ProjectResponseAction $project_response_action
     * @param App\UseCases\LINE\TodoResponseAction $todo_response_action
     * @param \App\UseCases\Line\Todo\RenameTodo $rename_todo
     */
    public function __construct(
        ProjectResponseAction $project_response_action,
        TodoResponseAction $todo_response_action,
        \App\UseCases\Line\Todo\RenameTodo $rename_todo,
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->project_response_action = $project_response_action;
        $this->todo_response_action = $todo_response_action;
        $this->rename_todo = $rename_todo;
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

        $question_number = $line_user->question->question_number;
        if ($event->getText() === '振り返る') {
            $test = $this->bot->replyMessage(
                $event->getReplyToken(),
                CheckedTodo::createCheckTodoFlexMessage()
            );
            if ($line_user->question->checked_todo) $line_user->question->update(['checked_todo' => null, 'parent_uuid' => null]);
        } else if ($event->getText() === 'やること') {
            // リッチメニューからやることを選択
            $this->bot->replyMessage(
                $event->getReplyToken(),
                Todo::askAddOrList($line_user->name)
            );
            if ($line_user->question->checked_todo) $line_user->question->update(['checked_todo' => null, 'parent_uuid' => null]);
        } else if ($event->getText() === '使い方') {
            $this->bot->replyText(
                $event->getReplyToken(),
                'ごめんなさい！まだ使い方の説明を実装してないです！'
            );
            if ($line_user->question->checked_todo) $line_user->question->update(['checked_todo' => null, 'parent_uuid' => null]);
        } else if ($event->getText() === 'テスト') {
            $notify_todo_check = new NotifyTodoCheck();
            $notify_todo_check->invoke($event);
        } else if ($question_number === LineUsersQuestion::NO_QUESTION) {
            // 質問がない場合
        } else if ($question_number === LineUsersQuestion::PROJECT) {
            // プロジェクトに関する質問の場合
            $this->project_response_action->invoke($event, $line_user);
        } else if (
            $question_number === LineUsersQuestion::GOAL ||
            $question_number === LineUsersQuestion::TODO ||
            $question_number === LineUsersQuestion::HABIT
        ) {
            // TodoやGOALに関する質問の場合
            $this->todo_response_action->invoke($event, $line_user, $question_number);
        } else if ($question_number === LineUsersQuestion::RENAME_TODO) {
            $this->rename_todo->invoke($event, $line_user, $line_user->question->parent_uuid);
        }
        return;
    }
}
