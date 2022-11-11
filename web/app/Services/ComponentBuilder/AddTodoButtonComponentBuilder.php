<?php

namespace App\Services\ComponentBuilder;

use App\Models\Todo;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

/**
 * Todoのカル-セル生成クラス
 */
class AddTodoButtonComponentBuilder
{
    /**
     * Todoのサブタイトル（親Todo）のテキストコンポーネント生成ビルダー
     *
     * @param Todo $todo
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder[]
     */
    public static function createAddTodoComponentButton(Todo $todo)
    {
        $actions = [];
        $add_todo_btn = new ButtonComponentBuilder(
            new PostbackTemplateActionBuilder('遂げること・習慣の追加', 'action=SELECT_WHETHER_TO_ADD_TODO_OR_HABIT&todo_uuid=' . $todo->uuid)
        );
        $add_todo_btn->setHeight('sm');
        $add_todo_btn->setMargin('md');
        $actions[] = $add_todo_btn;
        return $actions;
    }
}
