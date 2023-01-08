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
        'date',
        'time',
        'created_at'
    ];

    const NO_THIRD_QUESTION = [
        '不安', '辛い', 'いらいら', '悲しい', '眠い', 'イライラ', '悔しい'
    ];

    const JA_EN = [
        '不安' => 'anxious',
        '辛い' => 'hard',
        '疲れた' => 'tired',
        '悲しい' => 'sad',
        'イライラ' => 'angry',
        '悔しい' => 'kuyashi',
        '無気力' => 'lethargic',
        'もやもや' => 'moyamoya',
        '嬉しい' => 'glad',
        '楽しい' => 'fun',
        '穏やか' => 'calm',
        '幸せ' => 'happy',
        'ワクワク' => 'wakuwaku'
    ];

    const EN_JA = [
        'anxious' => '不安',
        'hard' => '辛い',
        'tired' => '疲れた',
        'sad' => '悲しい',
        'angry' => 'イライラ',
        'kuyashi' => '悔しい',
        'lethargic' => '無気力',
        'moyamoya' => 'もやもや',
        'glad' => '嬉しい',
        'fun' => '楽しい',
        'calm' => '穏やか',
        'happy' => '幸せ',
        'wakuwaku' => 'ワクワク',
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
            // new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😪眠い', '眠い')),
            // new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😑無気力', '無気力')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😠イライラ', 'イライラ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('😤悔しい', '悔しい')),
            // new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('🤔もやもや', 'もやもや')),
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
        if (Feeling::EN_JA[$feeling_type] === '不安') {
            $messages = Feeling::questionAfterAskAboutFeelingIfAnxious($user);
        } else if (Feeling::EN_JA[$feeling_type] === '心配') {
            $messages = Feeling::questionAfterAskAboutFeelingIfWorry();
        } else if (Feeling::EN_JA[$feeling_type] === '辛い') {
            $messages = Feeling::questionAfterAskAboutFeelingIfHard();
        } else if (Feeling::EN_JA[$feeling_type] === '悲しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfSadness($user);
        } else if (Feeling::EN_JA[$feeling_type] === '疲れた') {
            $messages = Feeling::questionAfterAskAboutFeelingIfTired();
        } else if (Feeling::EN_JA[$feeling_type] === '眠い') {
            $messages = Feeling::questionAfterAskAboutFeelingIfSleepy();
        } else if (Feeling::EN_JA[$feeling_type] === '無気力') {
            $messages = Feeling::questionAfterAskAboutFeelingIfLethargic();
        } else if (Feeling::EN_JA[$feeling_type] === 'イライラ') {
            $messages = Feeling::questionAfterAskAboutFeelingIfAnger();
        } else if (Feeling::EN_JA[$feeling_type] === '悔しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfKuyashi();
        } else if (Feeling::EN_JA[$feeling_type] === 'もやもや') {
            $messages = Feeling::questionAfterAskAboutFeelingIfMoyamoya();
        } else if (Feeling::EN_JA[$feeling_type] === 'ない') {
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
     * サンクスメッセージの仕分け
     *
     * @param string $feeling_type
     * @param string $reply
     * @return
     */
    public static function sortThanksMessage(string $feeling_type, string $reply)
    {
        if (Feeling::EN_JA[$feeling_type] === '不安') {
            $messages = Feeling::thanksMessageWhenAnxious();
        } else if (Feeling::EN_JA[$feeling_type] === '心配') {
            $messages = Feeling::thanksMessageWhenWorry($reply);
        } else if (Feeling::EN_JA[$feeling_type] === '辛い') {
            $messages = Feeling::thanksMessageWhenHard();
        } else if (Feeling::EN_JA[$feeling_type] === '悲しい') {
            $messages = Feeling::thanksMessageWhenSadness();
        } else if (Feeling::EN_JA[$feeling_type] === '疲れた') {
            $messages = Feeling::thanksMessageWhenTired($reply);
        } else if (Feeling::EN_JA[$feeling_type] === '眠い') {
            $messages = Feeling::thanksMessageWhenSleepy();
        } else if (Feeling::EN_JA[$feeling_type] === '無気力') {
        } else if (Feeling::EN_JA[$feeling_type] === 'イライラ') {
            $messages = Feeling::thanksMessageWhenAnger();
        } else if (Feeling::EN_JA[$feeling_type] === '悔しい') {
            $messages = Feeling::thanksMessageWhenKuyashi($reply);
        } else if (Feeling::EN_JA[$feeling_type] === 'もやもや') {
        } else if (Feeling::EN_JA[$feeling_type] === 'ない') {
            $messages = Feeling::thanksMessageWhenNotApplicable($reply);
        }
        $multi_message = new MultiMessageBuilder();
        $multi_message->add($messages[0]);
        if (count($messages) > 1) {
            $multi_message->add($messages[1]);
        } else if (count($messages) > 2) {
            $multi_message->add($messages[2]);
        }
        return $multi_message;
    }

    /**
     *
     * 不安
     *
     */

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
     * 不安の時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenAnxious()
    {
        return [
            new TextMessageBuilder('アガトンに喋ってくれてありがとう！'),
            new TextMessageBuilder('アドバイスはできないけどアガトン聞くことは得意なのでまた不安なことがあったらアガトンに話しかけてみてください！')
        ];
    }


    /**
     *
     * 心配
     *
     */

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
     * 心配の時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenWorry($reply)
    {
        return [
            new TextMessageBuilder('「' . $reply . '」ことが心配なんですね。'),
            new TextMessageBuilder('どうしたらうまくいくか考えるのもいいかもしれません！'),
            new TextMessageBuilder('また気が向いたらアガトンに話してみてください！'),
        ];
    }


    /**
     *
     * 辛い
     *
     */

    /**
     * 辛いこと吐き出せメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfHard()
    {
        return [
            new TextMessageBuilder('今は辛い気持ちなんですね。' . "\n" . 'どんなことが辛いのかよかったらアガトンに全部吐き出してみてください。')
        ];
    }


    /**
     * 辛いの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenHard()
    {
        return [
            new TextMessageBuilder('アガトンに喋ってくれてありがとう。' . "\n" . '今日はゆっくり休んでおいしいもの食べて寝てください！'),
        ];
    }

    /**
     *
     * 悲しい
     *
     */

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
     * 悲しいの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenSadness()
    {
        return [
            new TextMessageBuilder('少し気持ちがスッキリしたりしましたか？'),
            new TextMessageBuilder(
                'また何かあったらアガトンに頼ってみてください！'
                    . "\n" . 'アドバイスはできないけどアガトン聞くことなら得意です！'
            ),
        ];
    }

    /**
     *
     * 疲れてる
     *
     */

    /**
     * 疲れてるなら休めメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfTired()
    {
        return [
            new TextMessageBuilder('疲れてるんですね。' . "\n" . 'お疲れ様です!'),
            new TextMessageBuilder('今日はどんなことをしてたんですか？'),
        ];
    }

    /**
     * 疲れてるの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenTired($reply)
    {
        return [
            new TextMessageBuilder('なるほど。「' . $reply . '」をしてたんですね！'),
            new TextMessageBuilder('今日は明日に備えて早くゆっくり休んでください！'),
        ];
    }


    /**
     *
     * 眠い
     *
     */

    /**
     * 眠い
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfSleepy()
    {
        return [
            new TextMessageBuilder('今は眠いんですね。' . "\n" . 'どうして眠い感じですか？')
        ];
    }

    /**
     * 眠い時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenSleepy()
    {
        return [
            new TextMessageBuilder('だから眠いんですね！' . "\n" . '明日に備えて今日はゆっくり寝るか、カフェイン取って目覚まして頑張ってください！'),
        ];
    }

    /**
     *
     * 無気力
     *
     */
    /**
     * 無気力の理由を聞くメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfLethargic()
    {
        $quick_reply = new QuickReplyMessageBuilder([
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('やることが沢山', 'やることが沢山')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('未来への不安', '未来への不安')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('睡眠不足', '睡眠不足')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('嫌なこと', '嫌なこと')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('疲れ', '疲れ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('心配', '心配')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('生活習慣の乱れ', '生活習慣の乱れ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('分からない', '分からない')),
        ]);
        return [
            new TextMessageBuilder('そうなんですね。' . "\n" . 'アガトンもやる気が起きないことがよくあります。'),
            new TextMessageBuilder('やる気が出ない時って不思議ですよね。' . "\n" . '何があるから今やる気が出ないと思いますか？', $quick_reply)
        ];
    }


    /**
     *
     * 怒り
     *
     */
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
     * 怒りのサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenAnger()
    {
        return [
            new TextMessageBuilder('スッキリすることができましたか？' . "\n" . 'またイライラした時があったらアガトンにぶつけてみてください！'),
        ];
    }

    /**
     *
     * 悔しい
     *
     */

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
     * 悔しいのサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenKuyashi($reply)
    {
        return [
            new TextMessageBuilder('「' . $reply . '」ことが悔しかったんですね。'),
            new TextMessageBuilder('悔しいと感じるということはもっと頑張りたいってことですね！'
                . "\n" . 'この気持ちを忘れずに頑張ってください！'),
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
            new TextMessageBuilder('どんなことで今もやもやしてますか？')
        ];
    }

    /**
     *
     * 該当なし
     *
     */
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
    /**
     * 該当なしのサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenNotApplicable($reply)
    {
        return [
            new TextMessageBuilder('「' . $reply . '」な気持ちなんですね。'),
            new TextMessageBuilder('また気が向いたらアガトンにお話してみてください！

            '),
        ];
    }
}
