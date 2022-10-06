<?php

namespace App\UseCases\Line\Todo;

use App\Models\User;
use App\Models\Todo;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use DateTime;

class SelectTodoListAction
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
     *
     */
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     * 受け取ったメッセージを場合分けしていく
     *
     * @param object $event
     * @return
     */
    public function invoke(object $event, User $line_user, string $action_value)
    {
        $today_date_time = new DateTime();
        $today = $today_date_time->format('Y-m-d');
        if ($action_value === 'ALL_TODO_LIST') {
            $todo_list = $line_user->todo;
        } else if ($action_value === 'WEEKLY_TODO_LIST') {
            $next_week_date_time = $today_date_time->modify('+1 week');
            $next_week = $next_week_date_time->format('Y-m-d');
            $todo_list = Todo::where('user_uuid', $line_user->uuid)
                ->whereBetween('date', [$today, $next_week])
                ->orderBy('date', 'asc')
                ->get();
        } else {
            $todo_list = [];
        }

        $todo_carousel_columns = [];
        foreach ($todo_list as $todo) {
            if (count($todo->accomplish) === 0) {
                $todo_carousel_columns[] = Todo::createBubbleContainer($todo, $action_value);
            }
        }

        if ($action_value === 'WEEKLY_TODO_LIST') {
            $over_due_todo_list = Todo::where('user_uuid', $line_user->uuid)
                ->where('date', '<', $today)
                ->orderBy('date', 'asc')
                ->get();
            foreach ($over_due_todo_list as $over_due_todo) {
                if (count($over_due_todo->accomplish) === 0) {
                    $todo_carousel_columns[] = Todo::createBubbleContainer($over_due_todo, $action_value);
                }
            }
        }

        // Todoが何件あるか報告するメッセージ
        $message = Todo::createTodoListTitleMessage($line_user, $action_value, $todo_carousel_columns);

        if (count($todo_carousel_columns) > 0) {
            $todo_carousels = new CarouselContainerBuilder($todo_carousel_columns);
            $flex_message = new FlexMessageBuilder(
                'やること一覧',
                $todo_carousels
            );
            $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $builder->add(
                new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message['text'])
            );
            $builder->add($flex_message);
        } else {
            $builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message['text']);
        }


        $this->bot->replyMessage(
            $event->getReplyToken(),
            $builder
        );
        return;
    }
}
