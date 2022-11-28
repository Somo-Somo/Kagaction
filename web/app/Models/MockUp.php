<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class MockUp extends Model
{
    use HasFactory;
    const GOAL_FLAG = "https://s12.aconvert.com/convert/p3r68-cdx67/a7tko-td40m.png";
    const TODO_TREE = "https://s12.aconvert.com/convert/p3r68-cdx67/auc2z-vpo3q.png";

    const CALENDER = "https://s12.aconvert.com/convert/p3r68-cdx67/am2bc-fwv58.png";
    const CALENDER_CHECK = "https://s12.aconvert.com/convert/p3r68-cdx67/ado8e-bw2su.png";
    const CALENDER_OVERDUE = "https://s12.aconvert.com/convert/p3r68-cdx67/ahvzz-od1nb.png";
    const CALENDER_TODAY = "https://s12.aconvert.com/convert/p3r68-cdx67/a72fk-v6owx.png";
    const CALENDER_WEEK = "https://s12.aconvert.com/convert/p3r68-cdx67/arsmn-i9a90.png";

    /**
     * プロジェクトのゴールを聞く
     *
     * @param string $user_name
     * @return
     */
    public static function askFeeling(string $user_name)
    {
        // $carousels = [
        //     ['text' => '順調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/a96wa-vrtal.png", "postback_data" => "順調"],
        //     ['text' => '楽しい', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/al2kg-d0h8j.png", "postback_data" => "順調"],
        //     ['text' => 'ワクワク', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/aaahl-m8z5k.png", "postback_data" => "順調"],
        //     ['text' => '穏やか', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/anssb-vmz8a.png", "postback_data" => "順調"],
        //     ['text' => '普通', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/a00b5-ob68k.png", "postback_data" => "順調"],
        //     ['text' => '疲れた', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/anq6g-ajo1o.png", "postback_data" => "順調"],
        //     ['text' => '不安', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/amktk-56m8y.png", "postback_data" => "順調"],
        //     ['text' => '落ち込んでる', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/atlrf-sunis.png", "postback_data" => "順調"],
        //     ['text' => '無気力', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/al52c-hug28.png", "postback_data" => "順調"],
        // ];
        $carousels = [
            ['text' => '絶好調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/al2kg-d0h8j.png", "postback_data" => "絶好調"],
            ['text' => '好調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/aaahl-m8z5k.png", "postback_data" => "絶好調"],
            ['text' => 'まあまあ', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/anssb-vmz8a.png", "postback_data" => "絶好調"],
            ['text' => '不調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/anq6g-ajo1o.png", "postback_data" => "絶好調"],
            ['text' => '絶不調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/atlrf-sunis.png", "postback_data" => "絶好調"],
        ];
        $text =  $user_name . 'さんの今の気分を教えて！';
        $bubble_container_builders = [];
        foreach ($carousels as $key => $value) {
            $img_component_builders = new ImageComponentBuilder($value['image_url']);
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
            $template_action_builder = new PostbackTemplateActionBuilder($value['text'], $value['postback_data']);
            $bubble_container_builder->setAction($template_action_builder);
            $bubble_container_builders[] = $bubble_container_builder;
        }
        $carousel_container = new CarouselContainerBuilder($bubble_container_builders);
        $ask_message = new TextMessageBuilder($text);
        $flex_message = new FlexMessageBuilder($text, $carousel_container);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add($ask_message);
        $multi_message->add($flex_message);
        return $multi_message;
    }
}
