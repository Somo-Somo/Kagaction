<?php

namespace App\Services\CarouselContainerBuilder;

use App\Models\Condition;
use App\Models\Diary;
use App\Models\Feeling;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

/**
 * Todoのカル-セル生成クラス
 */
class PageTransitionBtnCarouselContainerBuilder
{
    /**
     *
     * 話すで記録したものを表示するBubbleContainer
     *
     * @param int $current_page
     * @param int $talk_log_num // 記録の個数
     * @param int $limit_carousel_num // カルーセルに表示される最大の件数
     * @param string $type = 'prev' || $type = next
     * @param string $action_value
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
     */
    public static function createPageTransitionBtnBubbleContainer(
        int $current_page,
        int $talk_log_num,
        int $limit_carousel_num,
        string $type,
        string $action_value
    ) {
        $last_page = intval(ceil($talk_log_num / $limit_carousel_num));
        if ($type === 'prev') {
            $text = '前の' . $limit_carousel_num . '件を見る';
            $btn = new ButtonComponentBuilder(
                new PostbackTemplateActionBuilder(
                    $text,
                    'action=' . $action_value . '&page=' . $current_page - 1
                ),
                1
            );
        } else if ($type === 'next') {
            $next_carousel_num = intval($last_page) === intval($current_page + 1) ?
                $talk_log_num - ($limit_carousel_num -  (($current_page - 1) * 10)) : $limit_carousel_num;
            $text = '次の' . $next_carousel_num . '件を見る';
            $btn = new ButtonComponentBuilder(
                new PostbackTemplateActionBuilder(
                    $text,
                    'action=' . $action_value . '&page=' . $current_page + 1
                ),
                1
            );
        }
        $btn->setGravity('center');
        $body_box = new BoxComponentBuilder('vertical', [$btn]);
        $body_box->setSpacing('sm');

        $bubble_container = new BubbleContainerBuilder();
        $bubble_container->setBody($body_box);
        return $bubble_container;
    }
}
