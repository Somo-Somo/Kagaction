<?php

namespace App\UseCases\Line\Todo;

use App\Models\AccomplishTodo;
use App\Models\CheckedTodo;
use App\Models\User;
use App\Models\Todo;
use App\Models\Habit;
use App\Repositories\Todo\TodoRepositoryInterface;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;

class DeleteTodo
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
        $todo = Todo::where('uuid', $todo_uuid)->first();
        if ($action_type === 'DELETE_TODO') {
            $this->bot->replyMessage(
                $event->getReplyToken(),
                Todo::confirmDeleteTodo($todo)
            );
        } else if ($action_type === 'OK_DELETE_TODO') {
            //neo4j側のTodoを削除and削除されるTodo以下の子Todoを持ってくる
            $delete_childs = $this->todo_repository->fetchDeleteTodo([
                'uuid' => $todo_uuid,
                'user_uuid' => $line_user->uuid
            ]);
            $delete_childs = $delete_childs[0]['childs'];

            $delete_num = count($delete_childs) + 1;
            $message = '「' . $todo->name . '」を含む' . $delete_num . '件のやることが削除されました';

            // 返信メッセージ
            $this->bot->replyText(
                $event->getReplyToken(),
                $message
            );

            // 削除するTodoのuuidだけ配列に入れる
            $delete_uuids = [$todo_uuid];
            foreach ($delete_childs as $delete_child) {
                $delete_uuids[] = $delete_child['properties']->toArray()['uuid'];
            }

            Habit::whereIn('todo_uuid', $delete_uuids)->delete();
            AccomplishTodo::whereIn('todo_uuid', $delete_uuids)->delete();
            CheckedTodo::whereIn('todo_uuid', $delete_uuids)->delete();
            // SQLの方のTodoを削除
            Todo::whereIn('uuid', $delete_uuids)->delete();
            $this->todo_repository->destroy([
                'uuid' => $todo_uuid,
                'user_uuid' => $line_user->uuid
            ]);
        } else if ($action_type === 'NOT_DELETE_TODO') {
            // 削除のキャンセル
            // 返信メッセージ
            $this->bot->replyText(
                $event->getReplyToken(),
                '「' . $todo->name . '」の削除をキャンセルしました'
            );
        }

        return;
    }
}
