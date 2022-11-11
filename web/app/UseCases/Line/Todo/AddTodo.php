<?php

namespace App\UseCases\Line\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Models\LineUsersQuestion;
use App\Repositories\Todo\TodoRepositoryInterface;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;

class AddTodo
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
     * @param App\Repositories\Todo\TodoRepositoryInterface
     */
    protected $todo_repository;

    /**
     * @param App\Repositories\Todo\TodoRepositoryInterface $todo_repository_interface
     */
    public function __construct(TodoRepositoryInterface $todo_repository_interface)
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->todo_repository = $todo_repository_interface;
    }

    /**
     * Todoの名前を変更する
     *
     * @param object $event
     * @param User $line_user
     * @param string $todo_uuid
     * @return
     */
    public function invoke(object $event, User $line_user, string $action_type, string $todo_uuid)
    {
        $parent_todo = Todo::where('uuid', $todo_uuid)->first();
        if ($action_type === 'SELECT_WHETHER_TO_ADD_TODO_OR_HABIT') {
            $multi_message_builder = Todo::selectWhetherToAddTodoOrHabitMessageBuilder($parent_todo);
            $this->bot->replyMessage($event->getReplyToken(), $multi_message_builder);
        } else {
            // 返信メッセージ
            $this->bot->replyText($event->getReplyToken(), Todo::askTodoName($parent_todo, $action_type));
            $question_num = $action_type === 'ADD_TODO' ? LineUsersQuestion::TODO : LineUsersQuestion::HABIT;
            //質問の更新
            $line_user->question->update([
                'question_number' => $question_num,
                'parent_uuid' => $todo_uuid,
            ]);
        }
        return;
    }
}
