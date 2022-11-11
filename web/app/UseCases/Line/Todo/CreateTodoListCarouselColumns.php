<?php

namespace App\UseCases\Line\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Services\CarouselContainerBuilder\TodoCarouselContainerBuilder;
use App\UseCases\Line\Todo\IdentifyTodoCarouselButton;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use DateTime;
use Illuminate\Support\Facades\Log;

class CreateTodoListCarouselColumns
{
    /**
     * @param App\UseCases\Line\Todo\IdentifyTodoCarouselButton
     */
    protected $identify_todo_carousel_button;

    public function __construct()
    {
        $this->identify_todo_carousel_button = new IdentifyTodoCarouselButton();
    }

    /**
     * 受け取ったメッセージを場合分けしていく
     *
     * @param User $line_user
     * @param object $todo_list
     * @param string $action_type
     * @param string $current_page
     * @return
     */
    public function invoke(User $line_user, object $todo_list, string $action_type, string $current_page)
    {
        $current_page = intval($current_page);

        $today_date_time = new DateTime();
        $today = $today_date_time->format('Y-m-d');

        $todo_carousel_columns = [];
        foreach ($todo_list as $todo) {
            ## 達成していないtodoもしくは達成が今日じゃなくて過去に達成してるかつ習慣のtodo
            if (
                count($todo->accomplish) === 0 ||
                ($todo->accomplish->where('created_at', '<', date('Y-m-d'))->first() && count($todo->habit) > 0)
            ) {

                $actions = $this->identify_todo_carousel_button->invoke($todo, $action_type);
                $todo_carousel_columns[] = TodoCarouselContainerBuilder::createTodoBubbleContainer($todo, $actions);
            }
        }

        if (
            $action_type === 'CHECK_TODO_BY_TODAY' ||
            $action_type === 'CHECK_TODO_BY_THIS_WEEK'
        ) {
            $over_due_todo_list = Todo::where('user_uuid', $line_user->uuid)
                ->where('date', '<', $today)
                ->orderBy('date', 'desc')
                ->get();
            foreach ($over_due_todo_list as $over_due_todo) {
                if (count($over_due_todo->accomplish) === 0) {
                    $actions = $this->identify_todo_carousel_button->invoke($over_due_todo, $action_type);
                    $todo_carousel_columns[] = TodoCarouselContainerBuilder::createTodoBubbleContainer($over_due_todo, $actions);
                }
            }
        }

        $count_todo_carousel_column = count($todo_carousel_columns);

        // Todoが8件以上ある時
        if ($count_todo_carousel_column > 9) {
            $todo_carousel_limit = $current_page === 1 ? 9 : 10;
            $slice_start = $current_page === 1 ? 0 : 9 + (($current_page - 2) * 10);
            $todo_carousel_columns = array_slice($todo_carousel_columns, $slice_start, $todo_carousel_limit);
            $todo_carousel_columns[] = Todo::createViewMoreBubbleContainer($todo_carousel_limit, $current_page, $count_todo_carousel_column, $action_type);
        }

        // Todoが何件あるか報告するメッセージ
        if ($current_page === 1) {
            if ($action_type === 'CHECK_TODO_BY_TODAY' || $action_type ===  'NOTIFY_TODO_CHECK') {
                $todo_type = '今日までに遂げること';
            } elseif ($action_type === 'WEEKLY_TODO_LIST' ||  $action_type === 'CHECK_TODO_BY_THIS_WEEK') {
                $todo_type = '今週までに遂げること';
            } else {
                $todo_type = 'プロジェクト:「' . $line_user->question->project->name . '」の遂げること';
            }
            $report_message = TodoCarouselContainerBuilder::createCountTodoBubbleContainer($todo_type, $count_todo_carousel_column);
            array_unshift($todo_carousel_columns, $report_message);
        }

        $todo_carousels = new CarouselContainerBuilder($todo_carousel_columns);
        $flex_message = new FlexMessageBuilder(
            '遂げること一覧',
            $todo_carousels
        );

        return $flex_message;
    }
}
