<?php

namespace App\Models;

use App\Services\CarouselContainerBuilder\SelectInTalkCarouselContainerBuilder;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Condition extends Model
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
        'evaluation',
        'date',
        'time',
        'created_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'evaluation' => 'integer',
    ];

    const CONDITION_TYPE = ['なし', '絶不調', '不調', 'まあまあ', '好調', '絶好調'];
    const CONDITION_EMOJI = [null, '😣 絶不調', '🙁 不調', '😐 まあまあ', '🙂 好調', '😆 絶好調'];

    const CAROUSEL =  [
        // [
        //     'text' => '絶好調',
        //     'image_url' =>
        //     [
        //         'local' => "/o/condition%2Fgreat.png?alt=media&token=702583dd-e71c-467d-808b-27f926455a55",
        //         'production' => "/o/condition%2Fgreat.png?alt=media&token=378eee5e-1074-4ab9-8b67-6583d4d9666d"
        //     ],
        //     "postback_data" => "action=ANSWER_CONDITION&value=絶好調"
        // ],
        // [
        //     'text' => '好調',
        //     'image_url' => [
        //         'local' => "/o/condition%2Fgood.png?alt=media&token=622ba252-d170-46dd-9a62-731a01b5899a",
        //         'production' => "/o/condition%2Fgood.png?alt=media&token=981928c3-9509-4023-a57a-f284faab230f"
        //     ],
        //     "postback_data" => "action=ANSWER_CONDITION&value=好調"
        // ],
        // [
        //     'text' => 'まあまあ',
        //     'image_url' => [
        //         'local' => '/o/condition%2Fok.png?alt=media&token=c40e34d9-2bc9-468e-9cb0-d6cbf00f34ce',
        //         'production' => "/o/condition%2Fok.png?alt=media&token=bc1e3771-0503-4034-9641-cda6b32ecfa9",
        //     ],
        //     "postback_data" => "action=ANSWER_CONDITION&value=まあまあ"
        // ],
        // [
        //     'text' => '不調',
        //     'image_url' => [
        //         'local' => '/o/condition%2Fbad.png?alt=media&token=6583d734-1d00-4c6e-912f-32554e8109a6',
        //         'production' => "/o/condition%2Fbad.png?alt=media&token=d2586a9a-3d09-4d6d-9325-585a6f3b3257",
        //     ],
        //     "postback_data" => "action=ANSWER_CONDITION&value=不調"
        // ],
        // [
        //     'text' => '絶不調',
        //     'image_url' => [
        //         'local' => "/o/condition%2Fworse.png?alt=media&token=6cfdf57c-c8a8-4afa-970b-f702f3b843aa",
        //         'production' => "/o/condition%2Fworse.png?alt=media&token=855a5b34-4b3c-4fa9-9546-98cadda1f0f9",
        //     ],
        //     "postback_data" => "action=ANSWER_CONDITION&value=絶不調"
        // ],
        [
            'text' => '絶好調',
            'image_url' => "https://firebasestorage.googleapis.com/v0/b/agathon-prod.appspot.com/o/condition%2Fgreat.png?alt=media&token=378eee5e-1074-4ab9-8b67-6583d4d9666d",
            "postback_data" => "action=ANSWER_CONDITION&value=絶好調"
        ],
        [
            'text' => '好調',
            'image_url' => "https://firebasestorage.googleapis.com/v0/b/agathon-prod.appspot.com/o/condition%2Fgood.png?alt=media&token=981928c3-9509-4023-a57a-f284faab230f",
            "postback_data" => "action=ANSWER_CONDITION&value=好調"
        ],
        [
            'text' => 'まあまあ',
            'image_url' => "https://firebasestorage.googleapis.com/v0/b/agathon-prod.appspot.com/o/condition%2Fok.png?alt=media&token=bc1e3771-0503-4034-9641-cda6b32ecfa9",
            "postback_data" => "action=ANSWER_CONDITION&value=まあまあ"
        ],
        [
            'text' => '不調',
            'image_url' =>  "https://firebasestorage.googleapis.com/v0/b/agathon-prod.appspot.com/o/condition%2Fbad.png?alt=media&token=d2586a9a-3d09-4d6d-9325-585a6f3b3257",
            "postback_data" => "action=ANSWER_CONDITION&value=不調"
        ],
        [
            'text' => '絶不調',
            'image_url' => "https://firebasestorage.googleapis.com/v0/b/agathon-prod.appspot.com/o/condition%2Fworse.png?alt=media&token=855a5b34-4b3c-4fa9-9546-98cadda1f0f9",
            "postback_data" => "action=ANSWER_CONDITION&value=絶不調"
        ],
    ];
    const EVALUATION = [
        '絶不調' => 1,
        '不調' => 2,
        'まあまあ' => 3,
        '好調' => 4,
        '絶好調' => 5
    ];

    /**
     * アガトンからユーザーへの質問を記録するテーブル
     *
     */
    public function question()
    {
        return $this->hasOne(Question::class, 'condition_id', 'id');
    }

    /**
     * 気分と紐づく
     *
     */
    public function feelings()
    {
        return $this->hasMany(Feeling::class, 'condition_id', 'id');
    }

    /**
     * 日記と紐づく
     *
     */
    public function diary()
    {
        return $this->hasOne(Diary::class, 'condition_id', 'id');
    }

    /**
     * 調子を聞く
     *
     * @param string $user_name
     * @return
     */
    public static function askCondition(string $user_name)
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
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Condition::CONDITION_EMOJI[5], '絶好調')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Condition::CONDITION_EMOJI[4], '好調')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Condition::CONDITION_EMOJI[3], 'まあまあ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Condition::CONDITION_EMOJI[2], '不調')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Condition::CONDITION_EMOJI[1], '絶不調')),
        ];
        $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($first_message));
        $multi_message->add(new TextMessageBuilder($ask_feeling_message, $quick_reply_message_builder));
        return $multi_message;
    }

    /**
     * 調子を聞く
     *
     * @param User $user
     * @param Question $question
     * @return
     */
    public static function askConditionByCarousel(User $user, Question $question)
    {
        $user_name = $user->nickname ? $user->nickname : $user->name;
        if ($question->operation_type == 1) {
            $first_message = '「今の調子や気持ちについて話す」ですね！かしこまりました！';
            $ask_text = $user_name . 'さんの今の調子はどうですか？';
        } else {
            $first_message = '「今日の振り返りをする」ですね！かしこまりました！';
            $ask_text = $user_name . 'さんの今日の調子はどうですか？';
        }

        $carousel_container = SelectInTalkCarouselContainerBuilder::createSelectInTalkBubbleContainer(Condition::CAROUSEL);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($first_message));
        $multi_message->add(new TextMessageBuilder($ask_text));
        $multi_message->add(new FlexMessageBuilder($ask_text, $carousel_container));
        return $multi_message;
    }
}
