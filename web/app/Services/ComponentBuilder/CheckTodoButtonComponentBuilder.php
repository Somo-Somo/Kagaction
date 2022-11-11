<?php

namespace App\Services\ComponentBuilder;

use App\Models\Todo;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

/**
 * 振り返るボタンの生成クラス
 */
class CheckTodoButtonComponentBuilder
{
    /**
     * 振り返るのボタン生成
     *
     * @param Todo $todo
     * @return LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder[]
     */
    public static function createCheckTodoComponentButton(Todo $todo)
    {
        $actions = [];
        $add_todo_btn = new ButtonComponentBuilder(
            new PostbackTemplateActionBuilder('振り返る', 'action=CHECK_TODO&todo_uuid=' . $todo->uuid),
        );
        $add_todo_btn->setHeight('sm');
        $add_todo_btn->setMargin('md');
        $actions[] = $add_todo_btn;
        return $actions;
    }
}
