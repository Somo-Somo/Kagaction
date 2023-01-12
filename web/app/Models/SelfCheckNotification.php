<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\Constant\Flex\BubbleContainerSize;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

class SelfCheckNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_uuid',
        'time',
        'created_at'
    ];

    /**
     *
     * 時間設定するときのメッセージ
     *
     * @param string $day_of_week
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    public static function createSettingTimeMessageBuilder(string $meridiem)
    {
        // header
        $header_text_builder = new TextComponentBuilder($meridiem === 'AM' ? '午前' : '午後');
        $header_text_builder->setWeight('bold');
        $header_text_builder->setAlign('center');
        $header_text_builder->setOffsetTop('12px');
        $header_box = new BoxComponentBuilder('vertical', [$header_text_builder]);

        // body
        $body_box_contents = [];
        for ($column = 0; $column < 6; $column++) {
            $body_box_contents[] = new SeparatorComponentBuilder();
            $rows = [];
            for ($row = 0; $row < 2; $row++) {
                if ($meridiem === 'AM') {
                    $time =  $column + (6 * $row) < 10 ?
                        '0' . $column + (6 * $row) . ':00' : $column + (6 * $row) . ':00';
                } else if ($meridiem === 'PM') {
                    $time =  $column + 12 + (6 * $row) . ':00';
                }
                $data =  'action=SELF_CHECK_NOTIFICATION_TIME&value=' . $time;
                $button_component =  new ButtonComponentBuilder(
                    new PostbackTemplateActionBuilder($time, $data),
                );
                $button_component->setColor('#87cefa');
                $rows[] = $button_component;
                $row === 0 ? $rows[] = new SeparatorComponentBuilder() : false;
            }
            $body_box_contents[] = new BoxComponentBuilder('horizontal', $rows);
        }
        $body_box = new BoxComponentBuilder('vertical', $body_box_contents);

        //bubble
        $time_bubble_container = new BubbleContainerBuilder();
        $time_bubble_container->setHeader($header_box);
        $time_bubble_container->setBody($body_box);
        $time_bubble_container->setSize('kilo');

        return $time_bubble_container;
    }

    /**
     *
     * 日時設定のflex_message変換
     *
     * @param array $time_bubble_container
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    public static function selectDateTimeFlexMessageBuilder(array $time_bubble_container)
    {
        // flex message
        $flex_message = new FlexMessageBuilder(
            'セルフチェックの通知の追加',
            new CarouselContainerBuilder($time_bubble_container)
        );
        return $flex_message;
    }
}
