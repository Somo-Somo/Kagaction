<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * 絶好調
     */

    /**
     * first message
     * function askWhatIsHappened
     * @return string
     */
    public static function askWhatIsHappenedWhenUserIsGreat()
    {
        return 'それはもう天才！何か特別いいことがあったりしましたか？';
    }

    /**
     * 好調
     */

    /**
     * first message
     * function askWhatIsHappened
     * @return string
     */
    public static function askWhatIsHappenedWhenUserIsGood()
    {
        return 'それは最高だね！何かいいことがあったりしましたか？？';
    }

    /**
     * If Good or Great
     * second message
     * function askWhatIsHappened
     * @return string
     */
    public static function pleaseWriteWhatHappenedIsGoodOrGreat()
    {
        return 'どんなことがありましたか？？' . "\n" . 'アガトンにも教えて欲しいです！';
    }


    /**
     * まあまあ
     */
    /**
     * first message
     * function pleaseWriteWhatHappened
     * @param User $user
     * @return string
     */
    public static function pleaseWriteWhatHappenedIsNormal($user)
    {
        return 'まあまあだったんですね！' . "\n" . $user->name . '今日はどんなことしてましたか？？';
    }

    /**
     * 不調
     */
    /**
     * first message
     * function askAboutFeeling
     * @return string
     */
    public static function askAboutFeelingIfWorse()
    {
        return '今の自分の気持ちを表すものがこの中にあったりしますか？';
    }

    /**
     * いいことがあった時（絶好調または好調かつ）
     * @return string
     */
    public static function thanksMessageWhenSomothingGoodHappens()
    {
        return
            [
                new TextMessageBuilder('そうだったんだ！'
                    . "\n" . 'アガトンに教えてくれてありがとう！'
                    . "\n" . 'また気が向いたらお話聞かせて！')
            ];
    }

    /**
     * いいことがなかった時（絶好調または好調かつ）
     *
     * @param Question $uquestion
     * @return string
     */
    public static function thanksMessageWhenNothingGoodHappens($question)
    {
        return
            [
                new TextMessageBuilder('だから' . Condition::CONDITION_TYPE[$question->condition->evaluation] . 'だったんだ！'
                    . "\n" . 'アガトンに教えてくれてありがとう！'
                    . "\n" . 'また気が向いたらお話聞かせて！')
            ];
    }
}
