<?php

namespace App\Models;

use DateTime;
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
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

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
        $time = new DateTime();
        $now_hour = $time->format('H');
        if ($now_hour > 4 && $now_hour < 11) {
            $greeting = 'おはよう！';
        } else if ($now_hour >= 11 && $now_hour < 18) {
            $greeting = 'こんにちは！';
        } else {
            $greeting = 'こんばんは！';
        }
        $first_message =  $user_name . 'さん、' . $greeting;
        $ask_feeling_message = "今の調子はどうですか？";
        $quick_reply_buttons = [
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😆絶好調', '絶好調')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🙂好調', '好調')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😐まあまあ', 'まあまあ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🙁不調', '不調')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😣絶不調', '絶不調')),
        ];
        $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($first_message));
        $multi_message->add(new TextMessageBuilder($ask_feeling_message, $quick_reply_message_builder));
        return $multi_message;
    }
}
