<?php

namespace App\Services\CarouselContainerBuilder;

use App\Models\Condition;
use App\Models\Diary;
use App\Models\Feeling;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;

/**
 * Todoのカル-セル生成クラス
 */
class TalkLogCarouselContainerBuilder
{
    /**
     *
     * Todoをカウントした結果の数を表示するBubbleContainer
     *
     * @param Conditoin $condition
     * @param Feeling $feeling
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
     */
    public static function createTalkLogBubbleContainer(Condition $condition, Feeling $feeling = null, Diary $diary = null)
    {
        $date_emoji = '🗓';
        $date_emoji_component  = new TextComponentBuilder($date_emoji, 0);

        $date_text = '2022-01-14 08:00';
        $date_text_component  = new TextComponentBuilder($date_text);
        $date_text_component->setWeight('bold');
        $date_text_component->setSize('sm');
        $date_text_component->setOffsetStart('sm');

        $date_array = [$date_emoji_component, $date_text_component];
        $date_box = new BoxComponentBuilder('baseline', $date_array);

        $condition_emoji = '💪';
        $condition_emoji_component = new TextComponentBuilder($condition_emoji, 0);
        $condition_emoji_component->setSize('md');

        $condition_text = '😃 絶好調 ';
        $condition_text_component  = new TextComponentBuilder($condition_text);
        $condition_text_component->setMargin('sm');
        $condition_text_component->setSize('md');
        $condition_text_component->setWeight('bold');

        $condition_array = [$condition_emoji_component, $condition_text_component];
        $condition_box = new BoxComponentBuilder('baseline', $condition_array);

        $feeling_emoji = '❤️ :';
        $feeling_emoji_component = new TextComponentBuilder($feeling_emoji, 0);
        $feeling_emoji_component->setSize('md');

        $feeling_text = '😃 不安';
        $feeling_text_component  = new TextComponentBuilder($feeling_text);
        $feeling_text_component->setMargin('sm');
        $feeling_text_component->setSize('md');
        $feeling_text_component->setWeight('bold');

        $feeling_array = [$feeling_emoji_component, $feeling_text_component];
        $feeling_box = new BoxComponentBuilder('baseline', $feeling_array);

        $separator = new SeparatorComponentBuilder();
        $separator->setMargin('lg');

        $memo_title = 'メモ :';
        $memo_title_component = new TextComponentBuilder($memo_title, 0);
        $memo_title_component->setSize('xxs');
        $memo_title_component->setColor('#8c8c8c');

        $memo_text = '東京旅行がとても楽しかった。けどとても疲れました。';
        $memo_text_component  = new TextComponentBuilder($memo_text);
        $memo_text_component->setColor('#8c8c8c');
        $memo_text_component->setSize('xxs');
        $memo_text_component->setWrap(true);

        $memo_array = [$memo_title_component, $memo_text_component];
        $memo_box = new BoxComponentBuilder('vertical', $memo_array);

        $body_box_array = [$date_box, $condition_box, $feeling_box, $separator, $memo_box];
        $body_box = new BoxComponentBuilder('vertical', $body_box_array);
        $body_box->setSpacing('md');

        $bubble_container = new BubbleContainerBuilder();
        $bubble_container->setBody($body_box);
        return $bubble_container;
    }
}
