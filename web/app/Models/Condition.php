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
        ['text' => '絶好調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/ai7dz-6bymx.png", "postback_data" => "action=ANSWER_CONDITION&value=絶好調"],
        ['text' => '好調', 'image_url' => "https://firebasestorage.googleapis.com/v0/b/agathon-prod.appspot.com/o/%E5%A5%BD%E8%AA%BF.svg?alt=media&token=c3a11db1-f5d9-41ec-9725-68be61560cea", "postback_data" => "action=ANSWER_CONDITION&value=好調"],
        ['text' => 'まあまあ', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/awp2h-avjb5.png", "postback_data" => "action=ANSWER_CONDITION&value=まあまあ"],
        ['text' => '不調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/abova-tgwn5.png", "postback_data" => "action=ANSWER_CONDITION&value=不調"],
        ['text' => '絶不調', 'image_url' => "https://s12.aconvert.com/convert/p3r68-cdx67/a4slq-53hdi.png", "postback_data" => "action=ANSWER_CONDITION&value=絶不調"],
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
