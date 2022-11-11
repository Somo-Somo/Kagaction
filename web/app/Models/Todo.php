<?php

namespace App\Models;

use DateTime;
use Carbon\Carbon;
use App\Models\LineBotSvg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\BlockStyleBuilder;
use LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;

use Illuminate\Support\Facades\Log;

class Todo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_uuid',
        'name',
        'uuid',
        'parent_uuid',
        'project_uuid',
        'date',
        'accomplish',
        'depth',
        'created_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'depth' => 'integer',
    ];

    /**
     * Todoに紐づくProject
     *
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_uuid', 'uuid');
    }

    /**
     * 達成したTodo
     *
     */
    public function accomplish()
    {
        return $this->hasMany(AccomplishTodo::class, 'todo_uuid', 'uuid');
    }

    /**
     * 振り返りを行なったTodo
     *
     */
    public function checked()
    {
        return $this->hasMany(CheckedTodo::class, 'todo_uuid', 'uuid');
    }

    /**
     * Todoに紐づく習慣
     *
     */
    public function habit()
    {
        return $this->hasMany(Habit::class, 'todo_uuid', 'uuid');
    }

    // TodoList 表示系
    const TODO_LIST = [
        'ALL_TODO_LIST' => true,
        'SHOW_TODO_LIST_TO_ADD_TODO' => true
    ];

    /**
     * Todo追加
     */
    const ADD_TODO = [
        'SELECT_WHETHER_TO_ADD_TODO_OR_HABIT' => true,
        'ADD_TODO' => true,
        'ADD_HABIT' => true,
    ];

    /**
     * 日付をつける
     */
    const DATE = [
        'ASK_DATE_LIMIT' => true,
    ];

    const DELETE_TODO = [
        'DELETE_TODO' => true,
        'OK_DELETE_TODO' => true,
        'NOT_DELETE_TODO' => true
    ];

    const CHANGE_DATE = [
        'ASK_RESCHEDULE' => true,
        'RESCHEDULE' => true,
        'ASK_CHANGE_INTERVAL' => true,
        'CHANGE_INTERVAL' => true,
    ];

    /**
     *
     * ゴール Goal
     *
     */

    /**
     * プロジェクトのゴールを聞く
     *
     * @param string $user_name
     * @param string $project_name
     * @return string $reply_message
     */
    public static function askGoal(string $user_name, string $project_name)
    {
        return '今度は' . $user_name . 'さんが「' . $project_name . '」で達成したいゴールを教えて！';
    }


    /**
     *
     *
     * やること Todo
     *
     *
     */

    /**
     * Todoの名前を聞く
     *
     * @param Todo $todo
     * @param string $action_type
     * @return string $reply_message
     */
    public static function askTodoName(Todo $todo, string $action_type)
    {
        $to_achieve = $action_type === 'ADD_TODO' ? 'こと' : '習慣';
        return '「' . $todo->name . '」を遂げるためにやる' . $to_achieve . 'を教えてください！';
    }

    /**
     * 変更後のTodoの名前を聞く
     *
     * @param Todo $todo
     * @return string $reply_message
     */
    public static function askTodoReName(Todo $todo)
    {
        return '遂げること:「' . $todo->name . '」の変更後の名前を教えてください！';
    }

    /**
     * 名前の変更を確認する
     *
     * @param Todo $todo
     * @param DateTime $date
     * @return string $reply_message
     */
    public static function reportNewTodoName(Todo $todo, string $new_todo_name)
    {
        return '「' . $todo->name . '」から「' . $new_todo_name . '」へ変更が完了しました';
    }

    /**
     *
     *
     * やることの追加 AddTodo
     *
     *
     */

    /**
     *
     * 習慣を追加するのかやることを追加するのか聞く
     *
     * @param Todo $parent_todo
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    public static function selectWhetherToAddTodoOrHabitMessageBuilder(Todo $parent_todo)
    {
        $actions = [];
        $todo_or_habit = ['やること', '習慣'];

        foreach ($todo_or_habit as $key => $select_type) {
            $text_component  = new TextComponentBuilder($select_type, 1);
            $text_component->setWeight('bold');
            $text_component->setGravity('center');
            $text_component->setAlign('center');
            $text_component_builders = [$text_component];
            $action = $select_type === 'やること' ? 'ADD_TODO' : 'ADD_HABIT';
            $post_back_template_action = new PostbackTemplateActionBuilder(
                $select_type,
                'action=' . $action . '&todo_uuid=' . $parent_todo->uuid
            );
            $box_component = new BoxComponentBuilder('vertical', $text_component_builders);
            $box_component->setAction($post_back_template_action);
            $box_component->setHeight('80px');
            $bubble_container = new BubbleContainerBuilder();
            $bubble_container->setBody($box_component);
            $bubble_container->setSize('nano');
            $actions[] = $bubble_container;
        }
        $carousels = new CarouselContainerBuilder($actions);
        $question_message = '「' . $parent_todo->name . '」を遂げるために「やること」と「習慣」どちらを追加しますか？';

        $multi_message_builder = new MultiMessageBuilder();
        $multi_message_builder->add(new TextMessageBuilder($question_message));
        $multi_message_builder->add(new FlexMessageBuilder($question_message, $carousels));

        return $multi_message_builder;
    }

    /**
     * Todo追加した後どうするか
     *
     * @param Todo $todo
     * @param User $line_user
     * @param string $date
     * @return \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder
     */
    public static function createWhatToDoAfterAddingTodoCarousel(Todo $todo, User $line_user)
    {
        $parent_todo = Todo::where('uuid', $todo->parent_uuid)->first();
        $carousel_columns = [
            Todo::continueAddTodoOfTodo($todo),
            Todo::continueAddTodoOfParentTodo($parent_todo),
        ];
        if (!$line_user->question->checked_todo) $carousel_columns[] = Todo::comeBackTodoList($todo->project);
        return new CarouselTemplateBuilder($carousel_columns);
    }

    /**
     * 作ったTodoのTodoを新しく追加する
     *
     * @param object $todo
     * @return LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
     */
    public static function continueAddTodoOfTodo(object $todo)
    {
        $carouselText =  '「' . $todo->name . '」を遂げるためにやることを新しく追加しますか?';
        $actions = [
            new PostbackTemplateActionBuilder('追加する', 'action=SELECT_WHETHER_TO_ADD_TODO_OR_HABIT&todo_uuid=' . $todo->uuid),
        ];
        $builder = new CarouselColumnTemplateBuilder(null, $carouselText, null, $actions);
        return $builder;
    }

    /**
     * 引き続き親TodoのTodoを追加する
     *
     * @param object $parentTodo
     * @return LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
     */
    public static function continueAddTodoOfParentTodo(object $parent_todo)
    {
        $carouselText =  '「' . $parent_todo->name . '」を遂げるためにやることを引き続き追加しますか?';
        $actions = [
            new PostbackTemplateActionBuilder('追加する', 'action=SELECT_WHETHER_TO_ADD_TODO_OR_HABIT&todo_uuid=' . $parent_todo->uuid),
        ];
        $builder = new CarouselColumnTemplateBuilder(null, $carouselText, null, $actions);
        return $builder;
    }


    /**
     * Todoを変更する
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder
     */
    public static function changeTodo(Todo $todo)
    {
        $title =  '「' . $todo->name . '」';
        $change_date_postback = count($todo->habit) > 0 ?
            new PostbackTemplateActionBuilder('習慣の変更', 'action=ASK_CHANGE_INTERVAL&todo_uuid=' . $todo->uuid) :
            new PostbackTemplateActionBuilder('振り返る日の変更', 'action=ASK_RESCHEDULE&todo_uuid=' . $todo->uuid);
        return new TemplateMessageBuilder(
            $title,
            new ButtonTemplateBuilder(
                $title,
                "選択してください",
                null,
                [
                    new PostbackTemplateActionBuilder("名前の変更", 'action=RENAME_TODO&todo_uuid=' . $todo->uuid),
                    $change_date_postback
                ]
            )
        );
    }

    /**
     *
     *
     * やること一覧 TodoList
     *
     *
     */

    /**
     * Todoの一覧を見るか、それとも新しくTodoを追加するか尋ねる
     *
     * @param string $line_user_name
     * @return \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder
     */
    public static function askAddOrList(string $line_user_name)
    {
        $builder =
            new TemplateMessageBuilder(
                '遂げること', // チャット一覧に表示される
                new ButtonTemplateBuilder(
                    $line_user_name . 'さんの遂げること', // title
                    '選択してください', // text
                    null, // 画像url
                    [
                        new PostbackTemplateActionBuilder('一覧を見る', 'action=ALL_TODO_LIST&page=1'),
                        new PostbackTemplateActionBuilder('遂げることを追加する', 'action=SHOW_TODO_LIST_TO_ADD_TODO&page=1'),
                    ]
                )

            );
        return $builder;
    }

    /**
     * Todoの一覧表示
     *
     * @param User $line_user
     * @param string $action_value
     * @param array $todo_carousel_columns
     * @return \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder
     */
    public static function createTodoListTitleMessage(User $line_user, string $action_value, array $todo_carousel_columns)
    {
        if (
            $action_value === 'ALL_TODO_LIST' ||
            $action_value === 'SELECT_TODO_LIST_TO_CHECK'
        ) {
            $title = '「' . $line_user->question->project->name . '」の遂げること一覧';
            $text =   'プロジェクト:「' . $line_user->question->project->name . '」の遂げることは' . count($line_user->todo) . '件です';
        } else if ($action_value === 'CHECK_TODO_BY_THIS_WEEK') {
            $title = $line_user->name . 'さんの今週までに振り返ること一覧';
            $text = $line_user->name . 'さんの今週までに振り返ることは' . count($todo_carousel_columns) . '件です';
        } else if ($action_value === 'CHECK_TODO_BY_TODAY') {
            $title = $line_user->name . 'さんの今日までに振り返ること一覧';
            $text = $line_user->name . 'さんの今日までに振り返ることは' . count($todo_carousel_columns) . '件です';
        }
        return ['title' => $title, 'text' => $text];
    }

    /**
     * TodoListに戻る
     *
     * @param object $project
     * @return LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
     */
    public static function comeBackTodoList(object $project)
    {
        $carouselText =  '「' . $project->name . '」の遂げること一覧に戻りますか？';
        $actions = [
            new PostbackTemplateActionBuilder('戻る', 'action=SHOW_TODO_LIST_TO_ADD_TODO&page=1'),
        ];
        $builder = new CarouselColumnTemplateBuilder(null, $carouselText, null, $actions);
        return $builder;
    }


    /**
     *
     *
     * 遂げることの振り返り日 Date
     *
     *
     */

    /**
     * Todoの振り返る日を聞く
     *
     * @param string $user_name
     * @param array $todo
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder()
     */
    public static function askTodoLimited(string $user_name, array $todo)
    {
        $title = '「' . $todo['name'] . '」を遂げることができたか振り返る日';
        $text = 'それでは' . $user_name . 'さんはいつ「' . $todo['name'] . '」を遂げることができたか振り返りますか?';
        $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $builder->add(new TextMessageBuilder($text));
        $builder->add(
            new TemplateMessageBuilder(
                $title, // チャット一覧に表示される
                new ButtonTemplateBuilder(
                    $title, // title
                    '振り返る日を考えてみよう！', // text
                    null, // 画像url
                    [
                        new DatetimePickerTemplateActionBuilder('振り返る日を選択', 'action=ASK_DATE_LIMIT&todo_uuid=' . $todo['uuid'], 'date')
                    ]
                )
            )
        );
        return $builder;
    }

    /**
     * Todoの振り返る日を聞く
     *
     * @param string $user_name
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder()
     */
    public static function askReschedule(Todo $todo)
    {
        $date = new DateTime($todo->date);
        $builder =
            new TemplateMessageBuilder(
                '振り返る日の変更', // チャット一覧に表示される
                new ButtonTemplateBuilder(
                    '「' . $todo->name . '」', // title
                    '振り返る日: ' . $date->format('Y年m月d日'), // text
                    null, // 画像url
                    [
                        new DatetimePickerTemplateActionBuilder('振り返る日の変更', 'action=RESCHEDULE&todo_uuid=' . $todo->uuid, 'date')
                    ]
                )
            );
        return $builder;
    }

    /**
     * 日付を確認する
     *
     * @param Todo $todo
     * @param DateTime $date
     * @return string $reply_message
     */
    public static function confirmDate(Todo $todo, DateTime $date)
    {
        $confirm =  '「' . $date->format('Y年m月d日') . '」ですね！';
        $fighting =  'それでは' . $date->format('Y年m月d日') . 'に「' . $todo->name . '」が遂げることができたか振り返りましょう！';
        return $confirm . "\n" . $fighting;
    }

    /**
     * 変更後の日付を報告する
     *
     * @param Todo $todo
     * @param DateTime $date
     * @return string $reply_message
     */
    public static function confirmReschedule(Todo $todo, DateTime $new_date)
    {
        $old_date = new DateTime($todo->date);
        return '「' . $todo->name . '」の振り返る日を' . $old_date->format('Y年m月d日') . 'から' .  $new_date->format('Y年m月d日') . 'に変更しました';
    }

    /**
     * 日付の削除を確認する
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder
     */
    public static function confirmDeleteTodo(Todo $todo)
    {
        $text = '遂げること:「' . $todo->name . '」を削除してもよろしいですか？' . "\n" . '「' . $todo->name . '」を遂げるために設定した遂げることも全て削除されてしまいます。';
        $builder =
            new TemplateMessageBuilder(
                '削除の確認',
                new ConfirmTemplateBuilder(
                    $text, // title
                    [
                        new PostbackTemplateActionBuilder('はい', 'action=OK_DELETE_TODO&project_uuid=' . $todo->uuid),
                        new PostbackTemplateActionBuilder('いいえ', 'action=NOT_DELETE_TODO&todo_uuid=' . $todo->uuid),
                    ]
                )
            );
        return $builder;
    }

    /**
     *
     *
     * Flexメッセージのカラム
     *
     *
     */

    /**
     *
     * 一覧結果の数
     *
     */

    /**
     *
     * Todoをカウントした結果の数を表示するBubbleContainer
     *
     * @param User $line_user
     * @param string $todo_type
     * @param int $count_todo_list
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
     */
    public static function createCountTodoBubbleContainer(User $line_user, string $action_type, int $count_todo_list)
    {
        if ($action_type === 'ALL_TODO_LIST' || $action_type === 'SELECT_TODO_LIST_TO_CHECK') {
            $todo_type = 'プロジェクト:「' . $line_user->question->project->name . '」の遂げること';
        } elseif ($action_type === 'CHECK_TODO_BY_THIS_WEEK') {
            $todo_type = '今週までに振り返ること';
        } elseif ($action_type === 'CHECK_TODO_BY_TODAY' || $action_type ===  'NOTIFY_TODO_CHECK') {
            $todo_type = '今日までに振り返ること';
        }

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
     * Separator
     *
     **/

    /**
     * Body部分のセパレーターを作る
     *
     * @return \LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder
     */
    public static function createBubbleStyles()
    {
        $block_styles = new BlockStyleBuilder(null, true, null);
        $bubble_styles = new BubbleStylesBuilder();
        $bubble_styles->setBody($block_styles);
        return $block_styles;
    }

    /**
     *
     * カルーセルカラムが9(10)件超えた時
     *
     **/

    /**
     *
     * Todoカルーセルのページネーション
     *
     * @param int $todo_carousel_limit
     * @param int $current_page
     * @param int $count_todo_list
     * @param string $action_value
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
     */
    public static function createViewMoreBubbleContainer(int $todo_carousel_limit, int $current_page, int $count_todo_list, $action_value)
    {
        $last_page = intval(ceil($count_todo_list / $todo_carousel_limit));

        $contents = [];
        if ($current_page !== 1) {
            // 最初のページ以外の時
            $text = '前の' . $todo_carousel_limit . '件を見る';
            $prev_btn = new ButtonComponentBuilder(
                new PostbackTemplateActionBuilder(
                    $text,
                    'action=' . $action_value . '&page=' . $current_page - 1
                ),
                1 //flex
            );
            $prev_btn->setGravity('center');
            $contents[] = $prev_btn;
        }

        if ($current_page !== 1 && $current_page !== $last_page) {
            # 1ページ目でも最後のページでもない時
            $contents[] = new SeparatorComponentBuilder();
        }

        if ($current_page !== $last_page) {
            // ラストページ以外の時
            $next_todo_num = intval($last_page) === intval($current_page + 1) ? $count_todo_list -  (9 + (($current_page - 1) * 10)) : $todo_carousel_limit;
            $text = '次の' . $next_todo_num . '件を見る';
            $next_btn = new ButtonComponentBuilder(
                new PostbackTemplateActionBuilder(
                    $text,
                    'action=' . $action_value . '&page=' . $current_page + 1
                ),
                1 // flex
            );
            $next_btn->setGravity('center');
            $contents[] = $next_btn;
        }

        $body_box = new BoxComponentBuilder('vertical', $contents);
        $body_box->setSpacing('sm');

        $bubble_container = new BubbleContainerBuilder();
        $bubble_container->setBody($body_box);
        return $bubble_container;
    }
}
