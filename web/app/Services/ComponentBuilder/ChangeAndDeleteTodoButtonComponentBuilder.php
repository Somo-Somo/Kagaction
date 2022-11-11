<?php

namespace App\Services\ComponentBuilder;

use App\Models\Todo;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

/**
 * Todoのカル-セル生成クラス
 */
class ChangeAndDeleteTodoButtonComponentBuilder
{
    /**
     * Todoのサブタイトル（親Todo）のテキストコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder[]
     */
    public static function createChangeAndDeleteTodoComponentButton(Todo $todo)
    {
        $change_todo_btn = new ButtonComponentBuilder(
            new PostbackTemplateActionBuilder('名前・期限の変更', 'action=CHANGE_TODO&todo_uuid=' . $todo->uuid)
        );
        $change_todo_btn->setHeight('sm');
        $actions[] = $change_todo_btn;
        $add_todo_btn = new ButtonComponentBuilder(
            new PostbackTemplateActionBuilder('やることの削除', 'action=DELETE_TODO&todo_uuid=' . $todo->uuid)
        );
        $add_todo_btn->setHeight('sm');
        $add_todo_btn->setMargin('md');
        $add_todo_btn->setColor('#ff0000');
        $actions = [$change_todo_btn, $add_todo_btn];
        return $actions;
    }
}
