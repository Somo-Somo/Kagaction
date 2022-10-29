<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class Habit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     */
    protected $fillable = [
        'user_uuid',
        'todo_uuid',
        'interval',
        'day',
        'consecutive_days',
        'created_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'interval' => 'integer',
        'day' => 'integer',
        'consecutive_days' => 'integer',
    ];

    /**
     * ユーザーに紐づく習慣
     *
     * @return
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * todoに紐づく習慣
     *
     * @return
     */
    public function todo()
    {
        return $this->belongsTo(Todo::class, 'todo_uuid', 'uuid');
    }

    const HABIT = [
        'ASK_ABOUT_FREQUENCY' => true,
    ];

    const FREQUENCY = [
        '毎日' => 0,
        '毎週' => 1,
        '毎月' => 2,
        '平日' => 3,
        '週末' => 4,
    ];

    const DAY_OF_WEEK_ENGLISH = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    const DAY_OF_WEEK_CARBON = [Carbon::SUNDAY, Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY, Carbon::SATURDAY];

    const DAY_OF_WEEK_JA = ['日', '月', '火', '水', '木', '金', '土'];

    /**
     * どのくらいの頻度でやるか聞く
     *
     * @param array $parent_todo
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder()
     */
    public static function askFrequencyHabit(array $todo)
    {
        $actions = [];
        $frequencies = ['毎日', '毎週', '毎月', '平日', '週末'];

        foreach ($frequencies as $frequency) {
            $text_component  = new TextComponentBuilder($frequency, 1);
            $text_component->setWeight('bold');
            $text_component->setGravity('center');
            $text_component->setAlign('center');
            $text_component_builders = [$text_component];
            $post_back_template_action = new PostbackTemplateActionBuilder(
                $frequency,
                'action=ASK_ABOUT_FREQUENCY&value=' . $todo['uuid'] . ',' . Habit::FREQUENCY[$frequency] . ','
            );
            $box_component = new BoxComponentBuilder('vertical', $text_component_builders);
            $box_component->setAction($post_back_template_action);
            $box_component->setHeight('80px');
            $bubble_container = new BubbleContainerBuilder();
            $bubble_container->setBody($box_component);
            $bubble_container->setSize('nano');
            $actions[] = $bubble_container;
        }
        $carousels = new CarouselContainerBuilder($actions);
        $question_message = '「' . $todo['name'] . '」をどのくらいの頻度で行いますか？';

        $multi_message_builder = new MultiMessageBuilder();
        $multi_message_builder->add(new TextMessageBuilder($question_message));
        $multi_message_builder->add(new FlexMessageBuilder($question_message, $carousels));

        return $multi_message_builder;
    }

    /**
     * 何曜日にやるか選ぶ
     *
     * @param string $todo_uuid
     * @param string $frequency
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder()
     */
    public static function selectDayOfWeek(Todo $todo, string $frequency)
    {
        $actions = [];

        foreach (Habit::DAY_OF_WEEK_JA as $key => $day_of_week) {
            $text_component  = new TextComponentBuilder($day_of_week, 1);
            $text_component->setWeight('bold');
            $text_component->setGravity('center');
            $text_component->setAlign('center');
            $text_component_builders = [$text_component];
            $post_back_template_action = new PostbackTemplateActionBuilder(
                $day_of_week,
                'action=ASK_ABOUT_FREQUENCY&value=' . $todo->uuid . ',' . $frequency . ',' . $key
            );
            $box_component = new BoxComponentBuilder('vertical', $text_component_builders);
            $box_component->setAction($post_back_template_action);
            $box_component->setHeight('80px');
            $bubble_container = new BubbleContainerBuilder();
            $bubble_container->setBody($box_component);
            $bubble_container->setSize('nano');
            $actions[] = $bubble_container;
        }
        $carousels = new CarouselContainerBuilder($actions);
        $question_message = '「' . $todo->name . '」を毎週何曜日に行いますか？';

        $multi_message_builder = new MultiMessageBuilder();
        $multi_message_builder->add(new TextMessageBuilder($question_message));
        $multi_message_builder->add(new FlexMessageBuilder($question_message, $carousels));

        return $multi_message_builder;
    }

    /**
     * 毎月何日にやるか選ぶ
     *
     * @param string $todo_uuid
     * @param string $frequency
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function selectDayOfMonth(Todo $todo, string $frequency)
    {
        // 一つ目の質問メッセージ
        $question_message = '「' . $todo->name . '」を毎月何日に行いますか？';

        // header
        $header_text_builder = new TextComponentBuilder('日付を選択してください');
        $header_text_builder->setWeight('bold');
        $header_text_builder->setAlign('center');
        $header_text_builder->setOffsetTop('12px');
        $header_box = new BoxComponentBuilder('vertical', [$header_text_builder]);

        $body_box_contents = [];
        for ($row = 1; $row < 8; $row++) {
            $body_box_contents[] = new SeparatorComponentBuilder();
            $row_boxes = [];
            for ($column = 1; $column < 6; $column++) {
                if ($column === 1) $row_boxes[] = new SeparatorComponentBuilder();
                $date = (($row - 1) * 5) + $column;
                if ($date < 33) {
                    $date_text = $date !== 32 ? (string)$date : '末日';
                    $data = 'action=ASK_ABOUT_FREQUENCY&value=' . $todo->uuid . ',' . $frequency . ',' . $date;
                    $font_color = '#5f9ea0';
                    $button_component = new ButtonComponentBuilder(
                        new PostbackTemplateActionBuilder($date_text, $data)
                    );
                    $button_component->setColor($font_color);
                    $row_boxes[] = $button_component;
                    $row_boxes[] = new SeparatorComponentBuilder();
                }
            }
            $row_box = new BoxComponentBuilder('horizontal', $row_boxes);
            $row_box->setMaxHeight('50px');
            $body_box_contents[] = $row_box;
            if ($row === 7) $body_box_contents[] = new SeparatorComponentBuilder();
        }
        $body_box = new BoxComponentBuilder('vertical', $body_box_contents);

        //bubble
        $date_bubble_container = new BubbleContainerBuilder();
        $date_bubble_container->setHeader($header_box);
        $date_bubble_container->setBody($body_box);
        $date_bubble_container->setSize('mega');

        // flex message
        $flex_message = new FlexMessageBuilder(
            '日付を選択してください',
            $date_bubble_container
        );


        $multi_message_builder = new MultiMessageBuilder();
        $multi_message_builder->add(new TextMessageBuilder($question_message));
        $multi_message_builder->add($flex_message);

        return $multi_message_builder;
    }

    /**
     * 毎月何日にやるか選ぶ
     *
     * @param Todo $todo
     * @param int $frequency
     * @param int $day || null
     * @return string
     *
     **/
    public static function confirmHabit(Todo $todo, int $frequency, int $day = null)
    {
        if ($frequency === Habit::FREQUENCY['毎週']) {
            $day_text = Habit::DAY_OF_WEEK_JA[$day] . '曜日';
        } else if ($frequency === Habit::FREQUENCY['毎月']) {
            $day_text = $day !== 32 ? $day  . '日' : '末日';
        } else {
            $day_text = null;
        }
        $frequency_text = array_keys(Habit::FREQUENCY, $frequency)[0];
        $habit_date = $frequency_text . $day_text;
        $confirm =  '「' . $habit_date . '」ですね！';
        $fighting =  '「' . $todo->name . '」が' . '継続して達成できるように頑張っていきましょう！';
        return $confirm . "\n" . $fighting;
    }
}
