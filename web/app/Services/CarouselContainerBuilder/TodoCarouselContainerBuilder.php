<?php

namespace App\Services\CarouselContainerBuilder;

use Carbon\Carbon;
use App\Models\Todo;
use App\Models\AccomplishTodo;
use App\Models\Habit;
use App\Models\LineBotSvg;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;

/**
 * Todoのカル-セル生成クラス
 */
class TodoCarouselContainerBuilder
{
    /**
     *
     * Todoをカウントした結果の数を表示するBubbleContainer
     *
     * @param string $todo_type
     * @param int $count_todo_list
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
     */
    public static function createCountTodoBubbleContainer(string $todo_type, int $count_todo_list)
    {
        $result_count_todo_list_text = '📝' . ' ' . $count_todo_list;
        $result_count_todo_list_text_component  = new TextComponentBuilder($result_count_todo_list_text, 1);
        $result_count_todo_list_text_component->setGravity('bottom');
        $result_count_todo_list_text_component->setAlign('center');
        $result_count_todo_list_text_component->setSize('5xl');
        $result_count_todo_list_text_component->setOffsetBottom('8px');

        $report_count_todo_list_text = $todo_type . 'が' . $count_todo_list . '件見つかりました';
        $report_count_todo_list_text_component  = new TextComponentBuilder($report_count_todo_list_text, 1);
        $report_count_todo_list_text_component->setAlign('center');
        $report_count_todo_list_text_component->setWeight('bold');
        $report_count_todo_list_text_component->setWrap(true);

        $texts = [
            $result_count_todo_list_text_component,
            $report_count_todo_list_text_component
        ];
        $body_box = new BoxComponentBuilder('vertical', $texts);
        $body_box->setSpacing('lg');

        $bubble_container = new BubbleContainerBuilder();
        $bubble_container->setBody($body_box);
        return $bubble_container;
    }

    /**
     *
     * コンポーネントをひとまとめ。BubbleContainerの生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
     */
    public static function createTodoBubbleContainer(Todo $todo, array $actions)
    {
        $bubble_container = new BubbleContainerBuilder();
        $bubble_container->setHeader(TodoCarouselContainerBuilder::createHeaderComponent($todo));
        $bubble_container->setBody(new BoxComponentBuilder('vertical', $actions));
        return $bubble_container;
    }

    /**
     *
     * Header
     *
     **/

    /**
     *
     * ヘッダーに必要なコンポーネント総集め。Headerコンポーネントの生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder
     */
    public static function createHeaderComponent(Todo $todo)
    {
        $header_array = [
            TodoCarouselContainerBuilder::createSubtitleBoxComponent($todo),
            TodoCarouselContainerBuilder::createDateBoxComponent($todo),
            TodoCarouselContainerBuilder::createTitleComponent($todo),
            TodoCarouselContainerBuilder::createAccomplishGageComponent($todo),
        ];
        $header_component = new BoxComponentBuilder('vertical', $header_array);
        $header_component->setBackgroundColor('#ffffff');
        $header_component->setPaddingTop('16px');
        $header_component->setPaddingAll('12px');
        $header_component->setPaddingBottom('24px');

        return $header_component;
    }

    /**
     *
     * サブタイトル
     *
     **/

    /**
     * Todoのサブタイトル（親Todo）をひとまとめ。
     * Boxのコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder
     */
    public static function createSubtitleBoxComponent(Todo $todo)
    {
        $subtitle_text_component = TodoCarouselContainerBuilder::createSubtitleTextComponent($todo);
        $subtitle_icon_component = TodoCarouselContainerBuilder::createSubtitleIconComponent($todo);
        return new BoxComponentBuilder(
            'baseline',
            [$subtitle_icon_component, $subtitle_text_component]
        );
    }

    /**
     * Todoのサブタイトル（親Todo）のテキストコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder
     */
    public static function createSubtitleTextComponent(Todo $todo)
    {
        if ($todo->depth === 0) {
            $subtitle_text = 'プロジェクト:「' . $todo->project->name . '」のゴール';
        } else {
            $parent_todo = Todo::where('uuid', $todo->parent_uuid)->first();
            $todo_or_habit = count($todo->habit) > 0 ? '習慣' : 'こと';
            $subtitle_text = '「' . $parent_todo->name . '」のためにやる' . $todo_or_habit;
        }
        $subtitle_text_component = new TextComponentBuilder($subtitle_text);
        $subtitle_text_component->setSize("xxs");
        $subtitle_text_component->setColor("#aaaaaa");
        $subtitle_text_component->setMargin("4px");

        return $subtitle_text_component;
    }

    /**
     * Todoのサブタイトル（親Todo）のアイコンのコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder
     */
    public static function createSubtitleIconComponent(Todo $todo)
    {
        $url = $todo->depth === 0 ? LineBotSvg::GOAL_FLAG : LineBotSvg::TODO_TREE;
        $icon_component_builder = new IconComponentBuilder(
            $url, // 画像URL
            null, // margin
            "lg", // size
            null // aspectoRatio
        );
        $icon_component_builder->setOffsetTop('5px');
        return $icon_component_builder;
    }

    /**
     *
     * 日付
     *
     **/

    /**
     * 日付をひとまとめ
     * Boxコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder
     */
    public static function createDateBoxComponent(Todo $todo)
    {
        $date_text_component = TodoCarouselContainerBuilder::createDateTextComponent($todo);
        $date_icon_component = TodoCarouselContainerBuilder::createDateIconComponent($todo);
        $date_box_component = new BoxComponentBuilder(
            'baseline',
            [$date_icon_component, $date_text_component]
        );
        $date_box_component->setMargin('6px');
        return $date_box_component;
    }

    /**
     * 日付タイトルの日付のコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder
     */
    public static function createDateTextComponent(Todo $todo)
    {
        if ($todo->date) {
            $date = new Carbon($todo->date);
            if (
                count($todo->accomplish) > 0 &&
                !($todo->accomplish->where('created_at', '<', date('Y-m-d'))->first() && count($todo->habit) > 0)
            ) {
                $date_text = "達成";
            } else if ($date->isToday()) {
                $date_text = "今日まで";
            } else if ($date->isTomorrow()) {
                $date_text = "明日まで";
            } else if ($date->isPast()) {
                $date_text = $date->diffInDays(Carbon::now()->setTime(0, 0, 0)) . "日経過";
            } else if ($date->isFuture()) {
                $date_text = "残り" . $date->diffInDays(Carbon::now()->setTime(0, 0, 0)) . "日";
            }
        } else {
            $date_text = "日付:未設定";
        }

        $habit = Habit::where('todo_uuid', $todo->uuid)->first();
        if ($habit) {
            if ($habit->day && Habit::FREQUENCY['毎週'] === $habit->interval) {
                $date_text = $date_text . '（毎週' . Habit::DAY_OF_WEEK_JA[$habit->day] . '曜日）';
            } else if ($habit->day && Habit::FREQUENCY['毎月'] === $habit->interval) {
                $date_text = $habit->day !== 32 ?
                    $date_text . '（毎月' . $habit->day . '日）' :
                    $date_text . '（毎月末日）';
            } else {
                $date_text = $date_text . '（' . array_keys(Habit::FREQUENCY, $habit->interval)[0] . '）';
            }
        }

        $date_text_component = new TextComponentBuilder($date_text);
        $date_text_component->setMargin('4px');
        $date_text_component->setSize('sm');
        $date_text_component->setColor('#555555');
        $date_text_component->setWeight('bold');
        return $date_text_component;
    }

    /**
     * 日付メッセージのアイコンのコンポーネント生成ビルダーz
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder
     */
    public static function createDateIconComponent(Todo $todo)
    {
        if ($todo->date) {
            $date = new Carbon($todo->date);
            if (
                count($todo->accomplish) > 0 &&
                !($todo->accomplish->where('created_at', '<', date('Y-m-d'))->first() && count($todo->habit) > 0)
            ) {
                $icon_path = LineBotSvg::CALENDER_CHECK;
            } else if ($date->isToday()) {
                $icon_path = LineBotSvg::CALENDER_TODAY;
            } else if ($date->lte(Carbon::today()->addWeek()) && $date->gte(Carbon::today())) {
                $icon_path = LineBotSvg::CALENDER_WEEK;
            } else if ($date->lt(Carbon::today())) {
                $icon_path = LineBotSvg::CALENDER_OVERDUE;
            } else {
                $icon_path = LineBotSvg::CALENDER;
            }
        } else {
            $icon_path = LineBotSvg::CALENDER;
        }

        $icon_component = new IconComponentBuilder($icon_path);
        $icon_component->setSize('lg');
        $icon_component->setOffsetTop('5px');
        return $icon_component;
    }

    /**
     *
     * タイトル
     *
     **/

    /**
     * Todoのタイトルのコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder
     */
    public static function createTitleComponent(Todo $todo)
    {
        $title_component = new TextComponentBuilder($todo->name);
        $title_component->setSize('xl');
        $title_component->setMargin('6px');
        $title_component->setWrap(true);
        $title_component->setWeight('bold');
        return $title_component;
    }


    /**
     *
     * 達成度ゲージ
     *
     **/

    /**
     * Todoの完了のゲージのコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder
     */
    public static function createAccomplishGageComponent(Todo $todo)
    {
        $accomplished_percentage = TodoCarouselContainerBuilder::calcAccomplishedPercentage($todo);

        $accomplished_percentage_text = new TextComponentBuilder($accomplished_percentage);
        $accomplished_percentage_text->setSize('xs');
        $accomplished_percentage_text->setAlign('end');

        $accomplished_gage = new BoxComponentBuilder('vertical', []);
        $accomplished_gage->setWidth($accomplished_percentage);
        $accomplished_gage->setBackgroundColor('#0D8186');
        $accomplished_gage->setHeight('6px');

        $accomplish_gage = new BoxComponentBuilder('vertical', [$accomplished_gage]);
        $accomplish_gage->setBackgroundColor('#9FD8E36E');
        $accomplish_gage->setHeight('6px');
        $accomplish_gage->setMargin('sm');

        $accomplish_gage_component = new BoxComponentBuilder(
            'vertical',
            [$accomplished_percentage_text, $accomplish_gage]
        );
        $accomplish_gage_component->setMargin('sm');

        return $accomplish_gage_component;
    }

    /**
     * Todoの完了のゲージのコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return string $accomplished_percentage
     */
    public static function calcAccomplishedPercentage(Todo $todo)
    {
        $child_todo = Todo::where('parent_uuid', $todo->uuid)->pluck('uuid');
        if ($child_todo->count() > 0) {
            $accomplished_child_todo_num = AccomplishTodo::whereIn('todo_uuid', $child_todo)->get();
            $accomplished_percentage = $accomplished_child_todo_num ?
                round(count($accomplished_child_todo_num) / count($child_todo) * 100, 0) . '%' : '0%';
        } else {
            $accomplished_percentage = '0%';
        }
        return $accomplished_percentage;
    }
}
