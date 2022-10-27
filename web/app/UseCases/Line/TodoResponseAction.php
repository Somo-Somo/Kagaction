<?php

namespace App\UseCases\Line;

use App\Models\User;
use App\Models\Todo;
use App\Models\Habit;
use App\Models\LineUsersQuestion;
use App\Repositories\Goal\GoalRepositoryInterface;
use App\Repositories\Todo\TodoRepositoryInterface;
use App\Repositories\Line\LineBotRepositoryInterface;
use Illuminate\Support\Str;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use DateTime;


class TodoResponseAction
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
     * @param App\Repositories\Line\LineBotRepositoryInterface
     */
    protected $line_bot_repository;

    /**
     * @param App\Repositories\Goal\GoalRepositoryInterface
     */
    protected $goal_repository;

    /**
     * @param App\Repositories\Todo\TodoRepositoryInterface
     */
    protected $todo_repository;

    /**
     * @param App\Repositories\Line\LineRepositoryInterface $line_repository_interface
     * @param App\Repositories\Project\ProjectRepositoryInterface $project_repository_interface
     * @param App\Repositories\Goal\GoalRepositoryInterface $todo_repository_interface
     * @param App\Repositories\Todo\TodoRepositoryInterface $todo_repository_interface
     */
    public function __construct(
        LineBotRepositoryInterface $line_bot_repository_interface,
        GoalRepositoryInterface $goal_repository_interface,
        TodoRepositoryInterface $todo_repository_interface,
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->line_bot_repository = $line_bot_repository_interface;
        $this->goal_repository = $goal_repository_interface;
        $this->todo_repository = $todo_repository_interface;
    }

    /**
     * 受け取ったメッセージを場合分けしていく
     *
     * @param object $event
     * @return
     */
    public function invoke(object $event, User $line_user, int $question_number)
    {
        $parentTodo = Todo::where('uuid', $line_user->question->parent_uuid)->first();
        $depth = $parentTodo ? (int)$parentTodo->depth + 1 : 0;

        // TodoやGOALに関する質問の場合
        $todo = [
            'name' => $event->getText(),
            'uuid' => (string) Str::uuid(),
            'parent_uuid' => $line_user->question->parent_uuid,
            'project_uuid' => $line_user->question->project_uuid,
            'user_uuid' => $line_user->uuid,
            'depth' => $depth
        ];

        $message_builder = $question_number === LineUsersQuestion::HABIT ?
            Habit::askFrequencyHabit($todo) : Todo::askTodoLimited($line_user->name, $todo);

        // 返信メッセージ(日付)
        $this->bot->replyMessage($event->getReplyToken(), $message_builder);

        // TodoのSQLへの保存
        Todo::create($todo);

        //質問の消す
        $line_user->question->update([
            'question_number' => LineUsersQuestion::NO_QUESTION,
            'parent_uuid' => null
        ]);

        // GraphDBに保存
        $question_number === LineUsersQuestion::GOAL ?
            $this->goal_repository->create($todo) : $this->todo_repository->create($todo);

        return;
    }
}
