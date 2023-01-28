<?php

namespace App\Models;

use GuzzleHttp\Psr7\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
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
        '悲しい' => 'sad',
        '疲れた' => 'tired',
        'イライラ' => 'angry',
        '無気力' => 'lethargic',
        '嬉しい' => 'glad',
        '楽しい' => 'fun',
        '穏やか' => 'calm',
        'ワクワク' => 'wakuwaku',
        // 'もやもや' => 'moyamoya',
        // '悔しい' => 'kuyashi',
        // '幸せ' => 'happy',
    ];

    const EN_JA = [
        'glad' => '嬉しい',
        'fun' => '楽しい',
        'calm' => '穏やか',
        'wakuwaku' => 'ワクワク',
        'anxious' => '不安',
        'hard' => '辛い',
        'sad' => '悲しい',
        'tired' => '疲れた',
        'angry' => 'イライラ',
        'lethargic' => '無気力',
        // 'moyamoya' => 'もやもや',
        // 'kuyashi' => '悔しい',
        // 'happy' => '幸せ',

    ];

    const FEELING_EMOJI = [
        'glad' => '🥰 嬉しい',
        'fun' => '😆 楽しい',
        'calm' => '😌 穏やか',
        'wakuwaku' => '😎 ワクワク',
        'anxious' => '😔 不安',
        'hard' => '😣 辛い',
        'sad' => '😭 悲しい',
        'tired' => '😫 疲れた',
        'angry' => '😠 イライラ',
        'lethargic' => '😑 無気力',
        'moyamoya' => '🤔 もやもや',
        // 'kuyashi' => '😤 悔しい',
    ];

    const RESPONSE_GOBI = [
        'なんですね', 'なんですね', 'だったんですね', 'だったんですね',
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
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['glad'], '嬉しい')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['fun'], '楽しい')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['calm'], '穏やか')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['wakuwaku'], 'ワクワク')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['angry'], 'イライラ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['lethargic'], '無気力')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['tired'], '疲れた')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['anxious'], '不安')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['hard'], '辛い')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder(Feeling::FEELING_EMOJI['sad'], '悲しい')),
            // new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ない', 'ない')),
        ];
    }

    /**
     * 今の気持ちを聞く
     *
     * @param string $feeling
     * @param User $user
     * @param Question $response
     * @return
     */
    public static function questionAfterAskAboutFeelingMessage(string $feeling_type, User $user, Question $question)
    {
        // 今or今日
        $op_num = intval($question->operation_type);
        if (Feeling::EN_JA[$feeling_type] === '嬉しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfGlad($user, $op_num);
        } elseif (Feeling::EN_JA[$feeling_type] === '楽しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfFun($op_num);
        } elseif (Feeling::EN_JA[$feeling_type] === '穏やか') {
            $messages = Feeling::questionAfterAskAboutFeelingIfCalm($user, $op_num);
        } elseif (Feeling::EN_JA[$feeling_type] === 'ワクワク') {
            $messages = Feeling::questionAfterAskAboutFeelingIfWakuwaku($op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '不安') {
            $messages = Feeling::questionAfterAskAboutFeelingIfAnxious($user, $op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '辛い') {
            $messages = Feeling::questionAfterAskAboutFeelingIfHard($op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '悲しい') {
            $messages = Feeling::questionAfterAskAboutFeelingIfSadness($user, $op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '疲れた') {
            $messages = Feeling::questionAfterAskAboutFeelingIfTired($op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '無気力') {
            $messages = Feeling::questionAfterAskAboutFeelingIfLethargic($op_num);
        } else if (Feeling::EN_JA[$feeling_type] === 'イライラ') {
            $messages = Feeling::questionAfterAskAboutFeelingIfAnger($op_num);
        } else if (Feeling::EN_JA[$feeling_type] === 'ない') {
            $messages = Feeling::questionAfterAskAboutFeelingIfNotApplicable($op_num);
        }
        // else if (Feeling::EN_JA[$feeling_type] === '悔しい') {
        //     $messages = Feeling::questionAfterAskAboutFeelingIfKuyashi();
        // }
        // else if (Feeling::EN_JA[$feeling_type] === '心配') {
        //     $messages = Feeling::questionAfterAskAboutFeelingIfWorry();
        // }
        // else if (Feeling::EN_JA[$feeling_type] === '眠い') {
        //     $messages = Feeling::questionAfterAskAboutFeelingIfSleepy();
        // }
        // else if (Feeling::EN_JA[$feeling_type] === 'もやもや') {
        //     $messages = Feeling::questionAfterAskAboutFeelingIfMoyamoya();
        // }
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
     * サンクスメッセージの仕分け
     *
     * @param Question $question
     * @param string $reply
     * @param User $user
     * @return
     */
    public static function sortThanksMessage(Question $question, string $reply, User $user)
    {
        // 今or今日
        $op_num = intval($question->operation_type);
        $feeling_type = $question->feeling->feeling_type;
        if (Feeling::EN_JA[$feeling_type] === '嬉しい') {
            $messages = Feeling::thanksMessageWhenGlad();
        } elseif (Feeling::EN_JA[$feeling_type] === '楽しい') {
            $messages = Feeling::thanksMessageWhenFun();
        } elseif (Feeling::EN_JA[$feeling_type] === '穏やか') {
            $messages = Feeling::thanksMessageWhenCalm();
        } elseif (Feeling::EN_JA[$feeling_type] === 'ワクワク') {
            $messages = Feeling::thanksMessageWhenWakuwaku();
        } else if (Feeling::EN_JA[$feeling_type] === '不安') {
            $messages = Feeling::thanksMessageWhenAnxious($op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '辛い') {
            $messages = Feeling::thanksMessageWhenHard($user, $op_num);
        } else if (Feeling::EN_JA[$feeling_type] === '悲しい') {
            $messages = Feeling::thanksMessageWhenSadness($reply);
        } else if (Feeling::EN_JA[$feeling_type] === '疲れた') {
            $messages = Feeling::thanksMessageWhenTired($reply);
        } else if (Feeling::EN_JA[$feeling_type] === '無気力') {
            $messages = Feeling::thanksMessageWhenLethargic($reply, $op_num);
        } else if (Feeling::EN_JA[$feeling_type] === 'イライラ') {
            $messages = Feeling::thanksMessageWhenAnger();
        } else if (Feeling::EN_JA[$feeling_type] === 'ない') {
            $messages = Feeling::thanksMessageWhenNotApplicable($reply);
        }
        // else if (Feeling::EN_JA[$feeling_type] === '心配') {
        //     $messages = Feeling::thanksMessageWhenWorry($reply);
        // }
        // else if (Feeling::EN_JA[$feeling_type] === '悔しい') {
        //     $messages = Feeling::thanksMessageWhenKuyashi($reply);
        // }
        // else if (Feeling::EN_JA[$feeling_type] === '眠い') {
        //     $messages = Feeling::thanksMessageWhenSleepy();
        // }
        // else if (Feeling::EN_JA[$feeling_type] === 'もやもや') {
        // }

        if ($op_num  === 0) {
            $messages[] = new TextMessageBuilder(
                'というような感じで記録することができます！' . "\n" . '記録したい際はメニューから「話す」を押してください！',
                new QuickReplyMessageBuilder([
                    new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('了解！', '了解！'))
                ])
            );
            // $messages[] = new TextMessageBuilder(
            //     'その時々のことを記録したい場合は「今の調子や気持ちについて話す」、' . "\n" . '1日の最後にまとめて振り返りたい場合は「今日の振り返りをする」を押してください！'
            // );
        } else if ($op_num  === 1) {
            $messages[] = new TextMessageBuilder('これからもアガトンに色々お話してくれると嬉しいです！');
            $messages[] = new TextMessageBuilder('これで「今の調子や気持ちについて話す」を終了します。');
        } else if ($op_num  === 2) {
            $quick_reply_button = [
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('振り返る', '振り返る')),
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('終了する', '終了する')),
            ];
            $messages[] = new TextMessageBuilder(
                '他にも今日行ったことについて振り返りますか？',
                new QuickReplyMessageBuilder($quick_reply_button)
            );
        }

        $multi_message = new MultiMessageBuilder();
        $multi_message->add($messages[0]);
        if (count($messages) > 1) {
            $multi_message->add($messages[1]);
        }
        if (count($messages) > 2) {
            $multi_message->add($messages[2]);
        }
        if (count($messages) > 3) {
            $multi_message->add($messages[3]);
        }
        return $multi_message;
    }

    /**
     *
     * 嬉しい
     *
     */

    /**
     * 嬉しいを吐き出せメッセージ
     *
     * @param User $user
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfGlad(User $user, int $op_num)
    {
        $user_name = $user->nickname ? $user->nickname : $user->name;
        return [
            new TextMessageBuilder('嬉しい気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '。アガトンもなんだか嬉しいです！'),
            new TextMessageBuilder('どんなところが' . $user_name . 'さんにとって嬉しかったですか？')
        ];
    }

    /**
     * 嬉しいの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenGlad()
    {
        return [
            new TextMessageBuilder('アガトンに嬉しかったことを共有してくれてありがとうございます！')
        ];
    }

    /**
     *
     * 楽しい
     *
     */

    /**
     * 楽しいを吐き出せメッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfFun(int $op_num)
    {
        return [
            new TextMessageBuilder('楽しい気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '！'),
            new TextMessageBuilder('どんなところが楽しかったですか？')
        ];
    }

    /**
     * 楽しいの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenFun()
    {
        return [
            new TextMessageBuilder('なるほど！そういったところが楽しかったのですね！'),
        ];
    }

    /**
     *
     * 嬉しい
     *
     */

    /**
     * 嬉しいを吐き出せメッセージ
     *
     * @param User $user
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfCalm(User $user, int $op_num)
    {
        $user_name = $user->nickname ? $user->nickname : $user->name;
        return [
            new TextMessageBuilder('穏やかな気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '！'),
            new TextMessageBuilder('どうして穏やかな気持ちになりましたか？')
        ];
    }

    /**
     * 嬉しいの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenCalm()
    {
        return [
            new TextMessageBuilder('だから穏やかな気持ちなんですね！' . "\n" . '教えてくれてありがとうございます！'),
        ];
    }

    /**
     *
     * ワクワク
     *
     */

    /**
     * なぜワクワク質問メッセージ
     *
     * @return array
     */
    public static function questionAfterAskAboutFeelingIfWakuwaku(int $op_num)
    {
        return [
            new TextMessageBuilder('ワクワクしている気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '！'),
            new TextMessageBuilder('どんなことに今ワクワクしているんですか！？')
        ];
    }

    /**
     * ワクワクの時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenWakuwaku()
    {
        return [
            new TextMessageBuilder('だからワクワクしている気持ちなんですね！' . "\n" . 'アガトンにワクワクを共有してくれてありがとうございます！'),
        ];
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
    public static function questionAfterAskAboutFeelingIfAnxious(User $user, int $op_num)
    {
        $user_name = $user->nickname ? $user->nickname : $user->name;
        return [
            new TextMessageBuilder('不安な気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '。アガトンもよく不安になります。'),
            new TextMessageBuilder($user_name . 'さんが今不安に思うことを全部アガトンに吐き出してみてください！')
        ];
    }

    /**
     * 不安の時のサンクスメッセージ
     *
     * @return array
     */
    public static function thanksMessageWhenAnxious(int $op_num)
    {
        return [
            new TextMessageBuilder('なるほど。だから不安な気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '。'),
            new TextMessageBuilder('アガトンにも教えてくれてありがとうございます！'),
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
    public static function questionAfterAskAboutFeelingIfHard(int $op_num)
    {
        return [
            new TextMessageBuilder('今は辛い気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '。' . "\n" . 'どんなことが辛いのかよかったらアガトンに全部吐き出してみてください。')
        ];
    }


    /**
     * 辛いの時のサンクスメッセージ
     *
     * @param User
     * @return array
     */
    public static function thanksMessageWhenHard(User $user, int $op_num)
    {
        $user_name = $user->nickname ? $user->nickname : $user->name;
        return [
            new TextMessageBuilder('だから' . $user_name . 'さんは辛い感情' . Feeling::RESPONSE_GOBI[$op_num] . '。'),
            new TextMessageBuilder('アガトンにも教えてくれてありがとうございます！'),
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
    public static function questionAfterAskAboutFeelingIfSadness(User $user, int $op_num)
    {
        $user_name = $user->nickname ? $user->nickname : $user->name;
        return [
            new TextMessageBuilder($user_name . 'さんは今悲しい気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '。'),
            new TextMessageBuilder('どんなことに悲しいと感じましたか？')
        ];
    }

    /**
     * 悲しいの時のサンクスメッセージ
     *
     * @param string $reply
     * @return array
     */
    public static function thanksMessageWhenSadness(string $reply)
    {
        return [
            new TextMessageBuilder('「' . $reply . '」に悲しいと感じたんですね。'),
            new TextMessageBuilder(
                '悲しいことは悪いことではないです！'
                    . "\n" . '悲しい気持ちを否定せずそのまま受け止めてみてください！'
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
    public static function questionAfterAskAboutFeelingIfTired(int $op_num)
    {
        return [
            new TextMessageBuilder('疲れているんですね。' . "\n" . 'お疲れ様です！'),
            new TextMessageBuilder('どういった部分が疲れましたか！？'),
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
            new TextMessageBuilder('なるほど。「' . $reply . '」が疲れたのですね！'),
            new TextMessageBuilder('お疲れの中アガトンにお話してくれてありがとうございます！'),
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
    public static function questionAfterAskAboutFeelingIfLethargic(int $op_num)
    {
        $quick_reply = new QuickReplyMessageBuilder([
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('やることが漠然', 'やることが漠然')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('やることが沢山', 'やることが沢山')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('嫌なこと', '嫌なこと')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('未来への不安や心配', '未来への不安や心配')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('睡眠不足', '睡眠不足')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('疲れ', '疲れ')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('生活習慣の乱れ', '生活習慣の乱れ')),
        ]);
        return [
            new TextMessageBuilder('そう' . Feeling::RESPONSE_GOBI[$op_num] . '。' . "\n" . 'アガトンもやる気が起きないことがよくあります。'),
            new TextMessageBuilder('やる気が出ない時って不思議ですよね。' . "\n" . '何があるから今やる気が出ないと思いますか？', $quick_reply)
        ];
    }

    /**
     * 無気力のサンクスメッセージ
     *
     * string $reply
     * @return array
     */
    public static function thanksMessageWhenLethargic(string $reply, int $op_num)
    {
        $text_message = new TextMessageBuilder('「' . $reply . '」があるから無気力' . Feeling::RESPONSE_GOBI[$op_num] . '。' . "\n" . 'ぜひ自分を否定せずそのまま受け止めてみてください。');
        return [
            $text_message,
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
    public static function questionAfterAskAboutFeelingIfAnger(int $op_num)
    {
        return [
            new TextMessageBuilder('イライラな気持ち' . Feeling::RESPONSE_GOBI[$op_num] . '。'),
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




    // /**
    //  *
    //  * 悔しい
    //  *
    //  */

    // /**
    //  * 悔しいメッセージ
    //  *
    //  * @return array
    //  */
    // public static function questionAfterAskAboutFeelingIfKuyashi()
    // {
    //     return [
    //         new TextMessageBuilder('悔しい気持ちなんですね。'),
    //         new TextMessageBuilder('どんなところが悔しかったですか？')
    //     ];
    // }
    //     /**
    //  * 悔しいのサンクスメッセージ
    //  *
    //  * @return array
    //  */
    // public static function thanksMessageWhenKuyashi($reply)
    // {
    //     return [
    //         new TextMessageBuilder('「' . $reply . '」ことが悔しかったんですね。'),
    //         new TextMessageBuilder('悔しいと感じるということはもっと頑張りたいってことですね！'
    //             . "\n" . 'この気持ちを忘れずに頑張ってください！'),
    //     ];
    // }

    //   /**
    //      *
    //      * 心配
    //      *
    //      */

    //     /**
    //      * 心配していることを可視化させるメッセージ
    //      *
    //      * @return array
    //      */
    //     public static function questionAfterAskAboutFeelingIfWorry()
    //     {
    //         return [
    //             new TextMessageBuilder('心配な気持ちなんですね。' . "\n" . 'これからどんなことが起きるのが心配ですか？')
    //         ];
    //     }

    //     /**
    //      * 心配の時のサンクスメッセージ
    //      *
    //      * @return array
    //      */
    //     public static function thanksMessageWhenWorry($reply)
    //     {
    //         return [
    //             new TextMessageBuilder('「' . $reply . '」ことが心配なんですね。'),
    //             new TextMessageBuilder('どうしたらうまくいくか考えるのもいいかもしれません！'),
    //             new TextMessageBuilder('また気が向いたらアガトンに話してみてください！'),
    //         ];
    //     }

    // /**
    //  *
    //  * 眠い
    //  *
    //  */

    // /**
    //  * 眠い
    //  *
    //  * @return array
    //  */
    // public static function questionAfterAskAboutFeelingIfSleepy()
    // {
    //     return [
    //         new TextMessageBuilder('今は眠いんですね。' . "\n" . 'どうして眠い感じですか？')
    //     ];
    // }

    // /**
    //  * 眠い時のサンクスメッセージ
    //  *
    //  * @return array
    //  */
    // public static function thanksMessageWhenSleepy()
    // {
    //     return [
    //         new TextMessageBuilder('だから眠いんですね！' . "\n" . '明日に備えて今日はゆっくり寝るか、カフェイン取って目覚まして頑張ってください！'),
    //     ];
    // }

    // /**
    //  * もやもやメッセージ
    //  *
    //  * @return array
    //  */
    // public static function questionAfterAskAboutFeelingIfMoyamoya()
    // {
    //     return [
    //         new TextMessageBuilder('もやもやしてるんですね'),
    //         new TextMessageBuilder('どんなことで今もやもやしてますか？')
    //     ];
    // }

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
    public static function questionAfterAskAboutFeelingIfNotApplicable(int $op_num)
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
            new TextMessageBuilder('また気が向いたらアガトンにお話してみてください！'),
        ];
    }
}
