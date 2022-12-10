<?php

namespace App\Models;

use GuzzleHttp\Psr7\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Feeling extends Model
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
        'condition_id',
        'feeling_type',
        'created_at'
    ];

    const NO_THIRD_QUESTION = [
        '不安', '辛い', 'いらいら', '悲しい',
    ];

    /**
     * アガトンからユーザーへの質問を記録するテーブル
     *
     */
    public function question()
    {
        return $this->hasOne(Question::class, 'feeling_id', 'id');
    }

    /**
     * 気持ちのリプライボタン
     *
     * @return array
     */
    public static function feelingQuickReplyBtn()
    {
        return [
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😔不安', '不安')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😢心配', '心配')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😣辛い', '辛い')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😭悲しい', '悲しい')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😫疲れた', '疲れた')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😪眠い', '眠い')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😑無気力', '無気力')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😠イライラ', 'イライラ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😤悔しい', '悔しい')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🤔もやもや', 'もやもや')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ない', 'ない')),
        ];
    }

    /**
     * 今の気持ちを聞く
     *
     * @param string $feeling
     * @param User $user
     * @return
     */
    public static function questionAfterAskAboutFeelingMessage(string $feeling_type, User $user)
    {
        if ($feeling_type === '不安') {
            $messages = Feeling::questionAfterAskAboutFeelingIfAnxious($user);
        } else if ($feeling_type === '心配') {
            $messages = Feeling::questionAfterAskAboutFeelingIfWorry();
        } else if ($feeling_type === '辛い') {
            $messages = Feeling::questionAfterAskAboutFeelingIfHard();
        } else if ($feeling_type === '悲しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfSadness($user);
        } else if ($feeling_type === '疲れた') {
            $messages = Feeling::questionAfterAskAboutFeelingIfTired();
        } else if ($feeling_type === '眠い') {
            $messages = Feeling::questionAfterAskAboutFeelingIfSleepy();
        } else if ($feeling_type === '無気力') {
            $messages = Feeling::questionAfterAskAboutFeelingIfLethargic();
        } else if ($feeling_type === 'イライラ') {
            $messages = Feeling::questionAfterAskAboutFeelingIfAnger();
        } else if ($feeling_type === '悔しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfKuyashi();
        } else if ($feeling_type === 'もやもや') {
            $messages = Feeling::questionAfterAskAboutFeelingIfMoyamoya();
        } else if ($feeling_type === 'ない') {
            $messages = Feeling::questionAfterAskAboutFeelingIfNotApplicable();
        }
        $multi_message = new MultiMessageBuilder();
        $multi_message->add($messages[0]);
        if (count($messages) > 1) {
            $multi_message->add($messages[1]);
        }
        return $multi_message;
    }

    /**
     * 不安を吐き出せメッセージ
     *
     * @param User $user
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfAnxious(User $user)
    {
        return [
            new TextMessageBuilder('不安な気持ちなんですね。アガトンもよく不安になります。'),
            new TextMessageBuilder($user->name . 'さんが今不安に思うことを全部アガトンに吐き出してみてください！')
        ];
    }

    /**
     * 心配していることを可視化させるメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfWorry()
    {
        return [
            new TextMessageBuilder('心配な気持ちなんですね。' . "\n" . 'これからどんなことが起きるのが心配ですか？')
        ];
    }

    /**
     * 辛いこと吐き出せメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfHard()
    {
        return [
            new TextMessageBuilder('今は辛い気持ちなんですね。' . "\n" . 'どんなことが辛いのかよかったらアガトンに全部吐き出してみて。')
        ];
    }

    /**
     * 悲しいこと吐き出せメッセージ
     *
     * @param User $user
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfSadness(User $user)
    {
        return [
            new TextMessageBuilder('悲しいことがあったんですね。'),
            new TextMessageBuilder('気が少しでも楽になるように、よかったら今' . $user->name . 'さんが思っていることを全部アガトンに吐き出してみてください。')
        ];
    }

    /**
     * 疲れてるなら休めメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfTired()
    {
        return [
            new TextMessageBuilder('疲れてるんですね。お疲れ様です！。' . "\n" . '今日は明日に備えて早くゆっくり休んでください！'),
        ];
    }

    /**
     * 眠い
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfSleepy()
    {
        $quick_reply = new QuickReplyMessageBuilder([
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('はい', 'はい')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('いいえ', 'いいえ')),
        ]);
        return [
            new TextMessageBuilder(
                '眠いんですね。お疲れ様です！' . "\n" . '今眠い理由はたくさん頑張ったからですか？',
                $quick_reply
            )
        ];
    }

    /**
     * 無気力の理由を聞くメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfLethargic()
    {
        return [
            new TextMessageBuilder('そうなんですね。' . "\n" . 'アガトンもやる気が起きないことがよくあります。'),
            new TextMessageBuilder('やる気が出ない時って不思議ですよね。' . "\n" . '何があるから今やる気が出ないと思いますか？')
        ];
    }

    /**
     * 怒りぶつけろメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfAnger()
    {
        return [
            new TextMessageBuilder('イライラすることがあったんですね。'),
            new TextMessageBuilder('そうしたら全部アガトンにイライラの気持ちをぶつけてみてください！')
        ];
    }

    /**
     * 悔しいメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfKuyashi()
    {
        return [
            new TextMessageBuilder('悔しい気持ちなんですね。'),
            new TextMessageBuilder('どんなところが悔しかったですか？')
        ];
    }
    /**
     * もやもやメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfMoyamoya()
    {
        return [
            new TextMessageBuilder('もやもやしてるんですね'),
            new TextMessageBuilder('どんなことでもやもやしてますか？')
        ];
    }

    /**
     * 該当しない場合メッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfNotApplicable()
    {
        return [
            new TextMessageBuilder('この中には当てはまるものがなかったんですね。'),
            new TextMessageBuilder('今の気持ちを表すとしたらどんな言葉が思い浮かびますか？')
        ];
    }
}
