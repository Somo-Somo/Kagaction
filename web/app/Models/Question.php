<?php

namespace App\Models;

use DateTime;
use Dotenv\Util\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use Illuminate\Support\Facades\Log;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'line_user_id',
        'condition_id',
        'feeling_id',
        'operation_type',
        'order_number',
        'created_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'operation_type' => 'integer',
        'order_number' => 'integer',
    ];

    /**
     *
     * operation_type = 1
     * Order Number
     *
     * 1 リッチメニュー: 話す
     * 2 絶好調~普通: 何かあったあるorない？, 不調・絶不調: 感情を聞く
     * 3 絶好調~絶不調(普通のぞく): 何かあった詳しく教えて
     * 4 Thanks Message
     */

    /**
     * 質問と該当する調子を紐付ける
     *
     */
    public function condition()
    {
        return $this->belongsTo(Condition::class, 'condition_id', 'id');
    }

    /**
     * 質問と該当する気分を紐付ける
     *
     */
    public function feeling()
    {
        return $this->belongsTo(Feeling::class, 'feeling_id', 'id');
    }

    /**
     * なにが起きたのかきく
     *
     * @param User $user
     * @param string $condition
     * @return
     */
    public static function whatAreYouTalkingAbout($user)
    {
        $time = new DateTime();
        $now_hour = $time->format('H');
        if ($now_hour > 4 && $now_hour < 11) {
            $greeting = 'おはよう！';
        } else if ($now_hour >= 11 && $now_hour < 18) {
            $greeting = 'こんにちは！';
        } else {
            $greeting = 'こんばんは！';
        }
        $first_message =  $user->name . 'さん、' . $greeting;
        $ask_message = 'どちらを行いますか？';
        $quick_reply_message_builder = new QuickReplyMessageBuilder([
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('今の調子や気持ちについて話す', '今の調子や気持ちについて話す')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('今日の振り返りをする', '今日の振り返りをする')),
        ]);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($first_message));
        $multi_message->add(new TextMessageBuilder($ask_message, $quick_reply_message_builder));
        return $multi_message;
    }

    /**
     * なにが起きたのかきく
     *
     * @param string $condition_value
     * @param Question $question
     * @return
     */
    public static function askWhatIsHappened(string $condition_value, Question $question)
    {
        if ($question->operation_type === 1) {
            $time = '今';
            $ask =  '今どんなことをしていましたか？';
            $soso_gobi_response = 'なんですね！';
            $bad_gobi_response = 'いんですね！';
        } else {
            $time = '今日';
            $ask =  '今日はどのようなことをしていましたか？';
            $soso_gobi_response = 'だったんですね！';
            $bad_gobi_response = 'かったんですね！';
        }

        if ($condition_value === '絶好調') {
            $ask_what_is_happened = 'それはもう天才だね！' . "\n" . $ask . "\n" . 'アガトンにも教えて欲しいです！';
        } else if ($condition_value === '好調') {
            $ask_what_is_happened = 'それは最高だね！' . "\n" . $ask . "\n" . 'アガトンにも教えて欲しいです！';
        } else if ($condition_value === 'まあまあ') {
            $ask_what_is_happened = $time . 'はまあまあな調子' . $soso_gobi_response . "\n" . 'ちなみに' . $ask;
        } else if ($condition_value === '不調') {
            $ask_what_is_happened = $time . 'はあまり調子が良くな' . $bad_gobi_response . "\n" . 'ちなみに' . $ask;
        } else if ($condition_value === '絶不調') {
            $ask_what_is_happened = $time . 'は調子が良くな' . $bad_gobi_response . "\n" . 'ちなみに' . $ask;
        }
        $text_message_builder = new TextMessageBuilder($ask_what_is_happened);
        return $text_message_builder;
    }

    /**
     * 今の気持ちを聞く
     *
     * @param Question $question
     * @return
     */
    public static function askAboutFeeling(Question $question)
    {
        if ($question->operation_type === 1) {
            $time = '今';
        } else {
            $time = '今日は';
        }

        // $first_message = $get_text . 'のですね！' . "\n" . 'アガトンに教えてくれてありがとうございます！';
        $first_message = 'なるほど！そのようなことを' . $time . 'していたのですね！' . "\n" . 'アガトンに教えてくれてありがとうございます！';
        // $ask_message = $get_text . '時の気持ちを表すものがこの中にあったりしますか？';
        $ask_message =  'そのことをしていた時の気持ちを表すものがこの中にあったりしますか？';

        $quick_reply_buttons = Feeling::feelingQuickReplyBtn();
        $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($first_message));
        $multi_message->add(new TextMessageBuilder($ask_message, $quick_reply_message_builder));
        return $multi_message;
    }

    /**
     * 気持ちを聞いた後
     *
     * @param Question $question
     * @param User $user
     * @param Feeling $feeling
     * @return
     */
    public static function questionAfterAskAboutFeeling(User $user, Feeling $feeling, Question $question)
    {
        $multi_message = Feeling::questionAfterAskAboutFeelingMessage($feeling->feeling_type, $user, $question);
        return $multi_message;
    }



    /**
     * ありがとうのメッセージ
     *
     * @param Question $question
     * @param string $reply
     * @param User $user
     * @return TextMessageBuilder
     */
    public static function thanksMessage(Question $question, string $reply, User $user)
    {
        $message = Feeling::sortThanksMessage($question, $reply, $user);
        return $message;
    }
}
