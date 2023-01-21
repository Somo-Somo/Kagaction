<?php

namespace App\Services\CarouselContainerBuilder;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

class SelectInTalkCarouselContainerBuilder
{
    /**
     *
     * 話すの時のカルーセル
     *
     * @param array $carousels
     * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
     */
    public static function createSelectInTalkBubbleContainer(array $carousels)
    {
        $bubble_container_builders = [];
        foreach ($carousels as $key => $value) {
            $image_url = config('app.app_env') === 'local' ?
                $value['image_url']['local'] : $value['image_url']['production'];
            $url = config('app.mix_firebase_access_url') . $image_url;
            $img_component_builders = new ImageComponentBuilder($url);
            $img_component_builders->setSize('xs');
            $img_component_builders->setOffsetTop('8px');
            $text_component_builders = new TextComponentBuilder($value['text']);
            $text_component_builders->setSize('xs');
            $text_component_builders->setWeight('bold');
            $text_component_builders->setAlign('center');
            $text_component_builders->setOffsetTop('20px');
            $body_box = new BoxComponentBuilder(
                'vertical',
                [$img_component_builders, $text_component_builders]
            );
            $body_box->setHeight('120px');
            $body_box->setWidth('120px');
            $body_box->setBackgroundColor('#FFFFDB');
            $bubble_container_builder = new BubbleContainerBuilder();
            $bubble_container_builder->setBody($body_box);
            $bubble_container_builder->setSize('nano');
            $template_action_builder = new PostbackTemplateActionBuilder(
                $value['text'],
                $value['postback_data'],
                null,
                "openKeyboard"
            );
            $bubble_container_builder->setAction($template_action_builder);
            $bubble_container_builders[] = $bubble_container_builder;
        }
        return new CarouselContainerBuilder($bubble_container_builders);
    }
}
