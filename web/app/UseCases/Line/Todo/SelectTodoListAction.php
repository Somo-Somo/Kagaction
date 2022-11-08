<?php

namespace App\UseCases\Line\Todo;

use App\Models\LineUsersQuestion;
use App\Models\User;
use App\Models\Todo;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
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
     * @param \App\UseCases\Line\Todo\CreateTodoListCarouselColumns
     */
    protected $create_todo_list_carousel;

    /**
     * @param \App\UseCases\Line\Todo\CreateTodoListCarouselColumns  $create_todo_list_carousel
     */
    public function __construct(
        \App\UseCases\Line\Todo\CreateTodoListCarouselColumns $create_todo_list_carousel
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->create_todo_list_carousel = $create_todo_list_carousel;
    }

    /**
     * 受け取ったメッセージを場合分けしていく
     *
     * @param object $event
     * @param User $line_user
     * @param string $action_value
     * @param string $current_page
     * @return
     */
    public function invoke(object $event, User $line_user, string $action_value, string $current_page)
    {
        $current_page = intval($current_page);

        $today_date_time = new DateTime();
        $today = $today_date_time->format('Y-m-d');
        if ($action_value === 'ALL_TODO_LIST') {
            $todo_list = $line_user->todo;
        } elseif ($action_value === 'WEEKLY_TODO_LIST') {
            $next_week_date_time = $today_date_time->modify('+1 week');
            $next_week = $next_week_date_time->format('Y-m-d');
            $todo_list = Todo::where('user_uuid', $line_user->uuid)
                ->whereBetween('date', [$today, $next_week])
                ->orderBy('date', 'asc')
                ->get();
        } else {
            $todo_list = [];
        }

        $flex_message = $this->create_todo_list_carousel->invoke(
            $line_user,
            $todo_list,
            $action_value,
            $current_page
        );

        $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $builder->add($flex_message);

        if (count($todo_list) === 0) {
            $user_todo = Todo::where('user_uuid', $line_user->uuid)->first();
            if ($user_todo) {
                $carousel_text = '今週までにやることがありません。やること一覧からやることを追加してみてください！';
                $actions = [
                    new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder('やること一覧へ', 'action=ALL_TODO_LIST&page=1'),
                ];
                $carousel_column_template_builder = [new CarouselColumnTemplateBuilder(null, $carousel_text, null, $actions)];
                $builder->add(new TemplateMessageBuilder('やること一覧へ', new CarouselTemplateBuilder($carousel_column_template_builder)));
            } else {
                $ask_goal_text = '「' . $line_user->project->first()->name . '」のゴールがありません！' . "\n" . '「' . $line_user->project->first()->name . '」で達成したいゴールを教えてください!';
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($ask_goal_text));
                $line_user->question->update(
                    [
                        'question_number' => LineUsersQuestion::GOAL,
                        'parent_uuid' => $line_user->project->first()->uuid,
                        'project_uuid' => $line_user->project->first()->uuid
                    ]
                );
            }
        }

        $this->bot->replyMessage(
            $event->getReplyToken(),
            $builder
        );
        return;
    }
}
