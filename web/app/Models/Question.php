<?php

namespace App\Models;

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
    public static function askWhatIsHappened(User $user, string $condition)
    {
        if ($condition === '絶好調') {
            $ask_what_is_happened = 'それはもう天才だね！' . "\n" . 'どんなことをしていたんですか？' . "\n" . 'アガトンにも教えて欲しいです！';
        } else if ($condition === '好調') {
            $ask_what_is_happened = 'それは最高だね！' . "\n" . 'どんなことをしていたんですか？' . "\n" . 'アガトンにも教えて欲しいです！';
        } else if ($condition === 'まあまあ') {
            $ask_what_is_happened = 'まあまあな調子なんですね！' . "\n" . 'ちなみに' .  'どのようなことを今していましたか？';
        }
        $text_message_builder = new TextMessageBuilder($ask_what_is_happened);
        return $text_message_builder;
    }

    /**
     * なにが起きたのかきく
     *
     * @param Question $question
     * @param User $user
     * @return
     */
    public static function askWhyYouAreInGoodCondition(Question $question, User $user)
    {
        $question->update(['order_number' => 4]);
        $condition = Condition::where('id', $question->condition_id)->first();
        $message = 'そうだったんですね!' . "\n" . 'そしたらどうして' . $user->name . 'さんは今' . Condition::CONDITION_TYPE[$condition->evaluation] . 'なのですか？';
        $text_message_builder = new TextMessageBuilder($message);
        return $text_message_builder;
    }

    /**
     * 今の気持ちを聞く
     *
     * @param Question $question
     * @param string $get_text
     * @return
     */
    public static function askAboutFeeling(Question $question, string $get_text)
    {
        $condition = Condition::where('id', $question->condition_id)->first();
        if ($condition->evaluation > 2) {
            // $first_message = $get_text . 'のですね！' . "\n" . 'アガトンに教えてくれてありがとうございます！';
            $first_message = 'なるほど！そのようなことをしていたのですね！' . "\n" . 'アガトンに教えてくれてありがとうございます！';
            // $ask_message = $get_text . '時の気持ちを表すものがこの中にあったりしますか？';
            $ask_message =  'その時の気持ちを表すものがこの中にあったりしますか？';
        } else {
            $first_message = '今日は' . $get_text . 'だったんですね。';
            $ask_message = '今の自分の気持ちを表すものがこの中にあったりしますか？';
        }

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
    public static function questionAfterAskAboutFeeling(User $user, Feeling $feeling)
    {
        $multi_message = Feeling::questionAfterAskAboutFeelingMessage($feeling->feeling_type, $user);
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
        $message = Feeling::sortThanksMessage($question->feeling->feeling_type, $reply, $user);
        $question->update([
            'condition_id' => null,
            'feeling_id' => null,
            'operation_type' => null,
            'order_number' => null
        ]);
        return $message;
    }
}
