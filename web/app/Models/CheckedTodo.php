<?php

namespace App\Models;

use App\UseCases\Line\Todo\CheckTodo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;

class CheckedTodo extends Model
{
    use HasFactory;

    const CHECK_TODO = [
        'SELECT_CHECK_TODO' => true,
        'CHECK_TODO_BY_TODAY' => 51,
        'CHECK_TODO_BY_THIS_WEEK' => 52,
        'SELECT_TODO_LIST_TO_CHECK' => 53,
        'CHECK_TODO' => true,
        'ACCOMPLISHED_TODO' => true,
        'NOT_ACCOMPLISHED_TODO' => true,
        'ADD_TODO_AFTER_CHECK_TODO' => true,
        'NOT_ADD_TODO_AFTER_CHECK_TODO' => true,
        'FINISH_CHECK_TODO' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, string, datetime>
     */
    protected $fillable = [
        'user_uuid',
        'todo_uuid',
        'created_at'
    ];

    /**
     * どのTodoたちを振り返るか尋ねる
     *
     * @return \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder
     */
    public static function askWhichCheckTodo()
    {
        $builder =
            new TemplateMessageBuilder(
                '振り返り', // チャット一覧に表示される
                new ButtonTemplateBuilder(
                    'どちらのやることを振り返りますか？', // title
                    '選択してください', // text
                    null, // 画像url
                    [
                        new PostbackTemplateActionBuilder('今日までにやること', 'action=CHECK_TODO_BY_TODAY&page=1'),
                        new PostbackTemplateActionBuilder('今週までにやること', 'action=CHECK_TODO_BY_THIS_WEEK&page=1'),
                        new PostbackTemplateActionBuilder('やること一覧から選択', 'action=SELECT_TODO_LIST_TO_CHECK&page=1'),
                    ]
                )
            );
        return $builder;
    }

    /**
     * Todoが達成したかどうか
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder
     */
    public static function askIfTodoHasBeenAccomplished(Todo $todo)
    {
        $text = '「' . $todo->name . '」について達成できましたか？';
        $builder =
            new ConfirmTemplateBuilder(
                $text,
                [
                    new PostbackTemplateActionBuilder('はい', 'action=ACCOMPLISHED_TODO&todo_uuid=' . $todo->uuid),
                    new PostbackTemplateActionBuilder('いいえ', 'action=NOT_ACCOMPLISHED_TODO&todo_uuid=' . $todo->uuid)
                ]
            );
        return $builder;
    }

    /**
     * Todoが達成しなかった時、そのTodoを達成させるために新しくTodoを追加するかどうか
     *
     * @param Todo $todo
     * @return \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder
     */
    public static function addTodoAfterCheckTodo(Todo $todo)
    {
        $text = '「' . $todo->name . '」を達成するためにやることを新しく追加しますか?';
        $builder =
            new ConfirmTemplateBuilder(
                $text,
                [
                    new PostbackTemplateActionBuilder('はい', 'action=ADD_TODO_AFTER_CHECK_TODO&todo_uuid=' . $todo->uuid),
                    new PostbackTemplateActionBuilder('いいえ', 'action=NOT_ADD_TODO_AFTER_CHECK_TODO&todo_uuid=' . $todo->uuid)
                ]
            );
        return $builder;
    }

    /**
     * 振り返りを続けるかどうか
     *
     * @param LineUsersQuestion $question
     * @return \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder
     */
    public static function askContinueCheckTodo(LineUsersQuestion $question)
    {
        if ($question->checked_todo === CheckedTodo::CHECK_TODO['CHECK_TODO_BY_TODAY']) {
            $title = '今日までにやることの振り返りを続けますか？';
            $action_type = 'CHECK_TODO_BY_TODAY';
        } else if ($question->checked_todo === CheckedTodo::CHECK_TODO['CHECK_TODO_BY_THIS_WEEK']) {
            $title = '今週までにやることの振り返りを続けますか？';
            $action_type = 'CHECK_TODO_BY_THIS_WEEK';
        } else if ($question->checked_todo === CheckedTodo::CHECK_TODO['SELECT_TODO_LIST_TO_CHECK']) {
            $title = '振り返りを続けますか？';
            $action_type = 'SELECT_TODO_LIST_TO_CHECK';
        }
        $builder =
            new ConfirmTemplateBuilder(
                $title, // text
                [
                    new PostbackTemplateActionBuilder('続ける', 'action=' . $action_type . '&todo_uuid='),
                    new PostbackTemplateActionBuilder('終了する', 'action=FINISH_CHECK_TODO&todo_uuid='),
                ]
            );
        return $builder;
    }



    /**
     * 振り返り終了のアナウンス
     *
     * @param LineUsersQuestion $question
     * @return string $text
     */
    public static function getTextMessageOfFinishCheckTodo(LineUsersQuestion $question)
    {
        if ($question->checked_todo === CheckedTodo::CHECK_TODO['CHECK_TODO_BY_TODAY']) {
            $text = '今日までにやることの振り返りを終了しました。';
        } else if ($question->checked_todo === CheckedTodo::CHECK_TODO['CHECK_TODO_BY_THIS_WEEK']) {
            $text = '今週までにやることの振り返りを終了しました。';
        } else if ($question->checked_todo === CheckedTodo::CHECK_TODO['SELECT_TODO_LIST_TO_CHECK']) {
            $text = '振り返りを終了しました。';
        }
        return $text;
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
     * 振り返りのメッセージカラムたちののBubble部分
     *
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
     *
     */
    public static function createReflectionBubblesContainer()
    {
    }



    /**
     *
     * 振り返りのメッセージカラムのBubble部分
     *
     * @param string $carousel_type
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
     *
     */
    public static function createReflectionBubbleContainer(string $carousel_type)
    {
        $reflection_body = CheckedTodo::createReflectionBodyContainer($carousel_type);
        $reflection_footer = CheckedTodo::createReflectionFooterContainer($carousel_type);
        $bubble_container = new BubbleContainerBuilder();
        $bubble_container->setBody($reflection_body);
        $bubble_container->setFooter($reflection_footer);
    }


    /**
     *
     * 振り返りのメッセージカラムのbody部分
     *
     * @param string $carousel_type
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
     *
     */
    public static function createReflectionBodyContainer(string $carousel_type)
    {
        if ($carousel_type === '振り返る') {
            $reflection_title = '✅';
            $reflection_text = $carousel_type;
            $reflection_title_component_flex = 1;
        } else if ($carousel_type === '今日') {
            $reflection_title = '📘' . ' ' . $carousel_type;
            $reflection_text = '今日までにやること';
            $reflection_title_component_flex = 2;
        } else if ($carousel_type === '今週') {
            $reflection_title = '📙' . ' ' . $carousel_type;
            $reflection_text = '今週までにやること';
            $reflection_title_component_flex = 2;
        } else if ($carousel_type === '全て') {
            $reflection_title = '📚' . ' ' . $carousel_type;
            $reflection_text = 'やること一覧から選択';
            $reflection_title_component_flex = 2;
        } else if ($carousel_type === '通知') {
            $reflection_title = '⏰' . ' ' . $carousel_type;
            $reflection_text = '振り返りの通知設定';
            $reflection_title_component_flex = 2;
        }

        $reflection_title_component = new TextComponentBuilder($reflection_title, $reflection_title_component_flex);
        $reflection_title_component->setWeight('bold');
        $reflection_title_component->setAlign('center');
        $reflection_title_component->setSize('5xl');
        $reflection_title_component->setOffsetBottom('8px');
        $reflection_title_component->setGravity('bottom');

        $reflection_text_component = new TextComponentBuilder($reflection_text, 1);
        $reflection_text_component->setWeight('bold');
        $reflection_text_component->setAlign('center');
        $reflection_text_component->setSize('xl');

        $body_texts = [$reflection_title_component, $reflection_text_component];
        $body_box = new BoxComponentBuilder('vertical', $body_texts);
        $body_box->setSpacing('xl');
        $body_box->setHeight('280px');
        return $body_box;
    }

    /**
     *
     * 振り返りのメッセージカラムのFooter部分
     *
     * @param string $carousel_type
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
     *
     */
    public static function createReflectionFooterContainer(string $carousel_type)
    {
        if ($carousel_type === '今日') {
            $label = '振り返る';
            $data = 'action=CHECK_TODO_BY_TODAY&page=1';
        } else if ($carousel_type === '今週') {
            $label = '振り返る';
            $data = 'action=CHECK_TODO_BY_THIS_WEEK&page=1';
        } else if ($carousel_type === '全て') {
            $label = '振り返る';
            $data = 'action=SELECT_TODO_LIST_TO_CHECK&page=1';
        } else if ($carousel_type === '通知') {
            $label = '通知';
            $data = 'action=CHECK_TODO_NOTIFICATION&uuid=';
        }
        $footer_button = new ButtonComponentBuilder(
            new PostbackTemplateActionBuilder($label, $data),
        );
        $footer_box = new BoxComponentBuilder('vertical', [$footer_button]);
        return $footer_box;
    }
}
