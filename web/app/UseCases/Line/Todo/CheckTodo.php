<?php

namespace App\UseCases\Line\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Repositories\Date\DateRepositoryInterface;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use DateTime;

class CheckTodo
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
    protected $todo_repository;

    /**
     * @param App\Repositories\Date\DateRepositoryInterface $todo_repository_interface
     */
    public function __construct(DateRepositoryInterface $todo_repository_interface)
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
        $today_date_time = new DateTime();
        $today = $today_date_time->format('Y-m-d');
        if ($action_type === 'CHECK_TODO_BY_TODAY') {
            $todo_list = Todo::where('user_uuid', $line_user->uuid)
                ->where('date', $today)->get();
        } else if ($action_type === 'CHECK_TODO_BY_THIS_WEEK') {
            $todo_list = Todo::where('user_uuid', $line_user->uuid)
                ->whereBetween('date', [$today_date_time->modify('+1 week')->format('Y-m-d'), $today])
                ->get();
        } else if ($action_type === 'SELECT_TODO_LIST_TO_CHECK') {
            $todo_list = $line_user->todo;
        }
        $over_due_todo_list = Todo::where('user_uuid', $line_user->uuid)
            ->where('date', '<', $today);

        $todo_carousel_columns = [];
        foreach ($todo_list as $todo) {
            if ($todo->accomplish === NULL) {
                $todo_carousel_columns[] = Todo::createCheckTodoCarouselColumn($todo);
            }
        }
        foreach ($over_due_todo_list as $over_due_todo) {
            if ($over_due_todo->accomplish === NULL) {
                $todo_carousel_columns[] = Todo::createCheckTodoCarouselColumn($over_due_todo);
            }
        }

        $todo_carousels = new CarouselTemplateBuilder($todo_carousel_columns);
        $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();

        $message = Todo::createTodoListTitleMessage($line_user, $action_type, $todo_list);
        $todo_carousels = new CarouselTemplateBuilder($todo_carousel_columns);
        $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message));
        $builder->add(new TemplateMessageBuilder('振り返り', $todo_carousels));
        $this->bot->replyMessage(
            $event->getReplyToken(),
            $builder
        );

        return;
    }
}
