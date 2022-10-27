<?php

namespace App\UseCases\Line\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Models\Habit;
use App\Repositories\Date\DateRepositoryInterface;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use DateTime;

class ChangeDate
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
     * @param App\Repositories\Date\DateRepositoryInterface
     */
    protected $date_repository;

    /**
     * @param App\Repositories\Date\DateRepositoryInterface $date_repository_interface
     */
    public function __construct(DateRepositoryInterface $date_repository_interface)
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->date_repository = $date_repository_interface;
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
        if ($action_type === 'ASK_RESCHEDULE') {
            $this->bot->replyMessage(
                $event->getReplyToken(),
                Todo::askReschedule($todo)
            );
        } else if ($action_type === 'RESCHEDULE') {
            $new_date = [
                'uuid' => $todo_uuid,
                'user_uuid' => $line_user->uuid,
                'date' => $event->getPostbackParams()['date']
            ];

            // 返信メッセージ
            $this->bot->replyText(
                $event->getReplyToken(),
                Todo::confirmReschedule($todo, new DateTime($new_date['date']))
            );

            // SQLのアップデート
            $todo->update([
                'date' => $new_date
            ]);

            // Neo4jのアップデート
            $this->date_repository->updateDate($new_date);
        } else if ($action_type === 'ASK_CHANGE_INTERVAL') {
            $multi_message_builder = Habit::askFrequencyHabit(['uuid' => $todo->uuid, 'name' => $todo->name]);
            $this->bot->replyMessage($event->getReplyToken(), $multi_message_builder);
        }

        return;
    }
}
