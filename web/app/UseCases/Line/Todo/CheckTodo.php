<?php

namespace App\UseCases\Line\Todo;

use App\Models\AccomplishTodo;
use App\Models\User;
use App\Models\Todo;
use App\Models\CheckedTodo;
use App\Models\LineUsersQuestion;
use App\Repositories\Todo\TodoRepositoryInterface;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
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
     * @param App\Repositories\Todo\TodoRepositoryInterface
     */
    protected $todo_repository;

    /**
     * @param \App\UseCases\Line\Todo\CreateTodoListCarouselColumns
     */
    protected $create_todo_list_carousel;

    /**
     * @param App\Repositories\Todo\TodoRepositoryInterface $todo_repository_interface
     * @param \App\UseCases\Line\Todo\CreateTodoListCarouselColumns  $create_todo_list_carousel
     */
    public function __construct(
        TodoRepositoryInterface $todo_repository_interface,
        \App\UseCases\Line\Todo\CreateTodoListCarouselColumns $create_todo_list_carousel
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->todo_repository = $todo_repository_interface;
        $this->create_todo_list_carousel = $create_todo_list_carousel;
    }

    /**
     * Todoの名前を変更する
     *
     * @param object $event
     * @param User $line_user
     * @param string $second_value
     * @return
     */
    public function invoke(
        object $event,
        User $line_user,
        string $action_type,
        string $second_value
    ) {
        $todo_uuid = null;
        $current_page = null;
        mb_strlen($second_value) === 36 ?
            $todo_uuid = $second_value :
            $current_page = $second_value ? intval($second_value) : 1;

        if ($action_type === 'FINISH_CHECK_TODO') {
            $message = CheckedTodo::getTextMessageOfFinishCheckTodo($line_user->question);
            $this->bot->replyText($event->getReplyToken(), $message);
            $line_user->question->update([
                'checked_todo' => null,
                'parent_uuid' => null,
            ]);
            return;
        }
        // Todoセレクト前
        if (!$todo_uuid) {
            $today_date_time = new DateTime();
            $today = $today_date_time->format('Y-m-d');
            if ($action_type === 'CHECK_TODO_BY_TODAY') {
                $todo_list = Todo::where('user_uuid', $line_user->uuid)
                    ->where('date', $today)->get();
            } elseif ($action_type === 'CHECK_TODO_BY_THIS_WEEK') {
                $next_week = $today_date_time->modify('+1 week')->format('Y-m-d');
                $todo_list = Todo::where('user_uuid', $line_user->uuid)
                    ->whereBetween('date', [$today, $next_week])
                    ->orderBy('date', 'asc')
                    ->get();
            } elseif ($action_type === 'SELECT_TODO_LIST_TO_CHECK') {
                $todo_list = $line_user->todo;
            }

            $todo_carousel_flex_message = $this->create_todo_list_carousel->invoke(
                $line_user,
                $todo_list,
                $action_type,
                $current_page
            );

            $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $builder->add($todo_carousel_flex_message);

            if (count($todo_list) === 0) {
                $user_todo = Todo::where('user_uuid', $line_user->uuid)->first();
                if ($user_todo) {
                    $something_todo_text = $action_type === 'CHECK_TODO_BY_TODAY' ? '今日までにやること' : '今週までにやること';
                    $carousel_text = $something_todo_text . 'がありません。やること一覧からやることを追加してみてください！';
                    $actions = [
                        new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder('やること一覧へ', 'action=SHOW_TODO_LIST_TO_ADD_TODO&page=1'),
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
                $this->bot->replyMessage($event->getReplyToken(), $builder);
            } else {
                $this->bot->replyMessage($event->getReplyToken(), $builder);
                // なんの振り返りをしているか記憶しておく
                $line_user->question->update([
                    'checked_todo' => CheckedTodo::CHECK_TODO[$action_type]
                ]);
            }
        } else {
            $todo = Todo::where('uuid', $todo_uuid)->first();
            if ($action_type === 'CHECK_TODO') {
                $builder = new TemplateMessageBuilder('振り返り', CheckedTodo::askIfTodoHasBeenAccomplished($todo));
                $this->bot->replyMessage($event->getReplyToken(), $builder);
            } else if ($action_type === 'ACCOMPLISHED_TODO') {
                $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('「' . $todo->name . '」の達成おめでとうございます！'));
                $builder->add(new TemplateMessageBuilder('振り返り', CheckedTodo::askContinueCheckTodo($line_user->question)));
                $this->bot->replyMessage($event->getReplyToken(), $builder);
                AccomplishTodo::updateOrCreate(
                    ['todo_uuid' => $todo_uuid],
                    ['user_uuid' => $line_user->uuid]
                );
                $this->todo_repository->updateAccomplish(
                    ['uuid' => $todo_uuid, 'user_uuid' => $line_user->uuid]
                );
            } else if ($action_type === 'NOT_ACCOMPLISHED_TODO') {
                $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('引き続き「' . $todo->name . '」の達成に向けて頑張っていきましょう！'));
                $builder->add(new TemplateMessageBuilder('やることの追加', CheckedTodo::addTodoAfterCheckTodo($todo)));
                $this->bot->replyMessage($event->getReplyToken(), $builder);;
            } else if ($action_type === 'ADD_TODO_AFTER_CHECK_TODO') {
                $multi_message_builder = Todo::selectWhetherToAddTodoOrHabitMessageBuilder($todo);
                $this->bot->replyMessage($event->getReplyToken(), $multi_message_builder);
                // なんのTodoのためのTodoを追加するか記憶しておく
                $line_user->question->update([
                    'question_number' => LineUsersQuestion::TODO,
                    'parent_uuid' => $todo->uuid,
                ]);
            } else if ($action_type === 'NOT_ADD_TODO_AFTER_CHECK_TODO') {
                $builder = new TemplateMessageBuilder('振り返り', CheckedTodo::askContinueCheckTodo($line_user->question, $action_type));
                $this->bot->replyMessage($event->getReplyToken(), $builder);
            }
        }
        return;
    }
}
