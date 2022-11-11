<?php

namespace App\Services\CarouselContainerBuilder;

use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;

/**
 * メニュー:その他を押すと表示されるカルーセルの生成クラス
 */
class OtherMenuCarouselContainerBuilder
{
    const OTHER_MENUS = [
        // [
        //     'text' => '🛠️ 使い方',
        //     'postback_data' => 'action=&value='
        // ],
        [
            'text' => '🔔 通知の設定',
            'postback_data' => 'action=IF_YOU_WANT_TO_SET_UP_NOTIFY_CHECK_TODO&value='
        ],
        [
            'text' => '📭 お問い合わせ',
            'postback_data' => 'action=CONTACT_OR_FEEDBACK&value='
        ]
    ];

    public static function createCarouselContainerBuilder()
    {
        $bubble_container_builders = [];
        for ($num = 0; $num < count(OtherMenuCarouselContainerBuilder::OTHER_MENUS); $num++) {
            $bubble_container_builders[] =
                OtherMenuCarouselContainerBuilder::createBubbleContainerBuilder(
                    OtherMenuCarouselContainerBuilder::OTHER_MENUS[$num]
                );
        }
        return new CarouselContainerBuilder($bubble_container_builders);
    }

    public static function createBubbleContainerBuilder($menu)
    {
        $text_component_builders = new TextComponentBuilder($menu['text']);
        $text_component_builders->setWeight('bold');
        $text_component_builders->setAlign('center');
        $text_component_builders->setSize('lg');

        $body_box = new BoxComponentBuilder('vertical', [$text_component_builders]);
        $body_box->setHeight('120px');
        $body_box->setJustifyContent('center');

        $bubble_container_builder = new BubbleContainerBuilder();
        $bubble_container_builder->setBody($body_box);
        $bubble_container_builder->setSize('micro');
        $template_action_builder = new PostbackTemplateActionBuilder($menu['text'], $menu['postback_data']);
        $bubble_container_builder->setAction($template_action_builder);

        return $bubble_container_builder;
    }
}
