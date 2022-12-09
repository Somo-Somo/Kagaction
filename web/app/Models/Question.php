<?php

namespace App\Models;

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
     * 質問と該当する調子を紐付ける
     *
     */
    public function condition()
    {
        return $this->belongsTo(Condition::class, 'condition_id', 'id');
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
            $ask_what_is_happened = Condition::askWhatIsHappenedWhenUserIsGreat();
        } else if ($condition === '好調') {
            $ask_what_is_happened = Condition::askWhatIsHappenedWhenUserIsGood();
        }

        $quick_reply_buttons = [
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ある', 'ある')),
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ない', 'ない')),
        ];
        $quick_reply_message_builder = new QuickReplyMessageBuilder($quick_reply_buttons);
        $text_message_builder = new TextMessageBuilder($ask_what_is_happened, $quick_reply_message_builder);
        return $text_message_builder;
    }

    /**
     * 起こったことを書いてもらうようお願いする
     *
     * @param Question $question
     * @param User $user
     * @return
     */
    public static function pleaseWriteWhatHappened(Question $question, User $user)
    {
        $question->update(['order_number' => 3]);
        Log::debug($question->condition->evaluation);
        if ($question->condition->evaluation > 3) {
            $message = Condition::pleaseWriteWhatHappenedIsGoodOrGreat();
        } else if ($question->condition->evaluation === 3) {
            $message = Condition::pleaseWriteWhatHappenedIsNormal($user);
        }
        $text_message_builder = new TextMessageBuilder($message);
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
        $message = 'そうなんだ!' . "\n" . 'そしたらどうして' . $user->name . 'さんは今' . Condition::CONDITION_TYPE[$condition->evaluation] . 'なの？';
        $text_message_builder = new TextMessageBuilder($message);
        return $text_message_builder;
    }

    /**
     * なにが起きたのかきく
     *
     * @param Question $question
     * @return
     */
    public static function thanksMessage(Question $question)
    {
        $condition = Condition::where('id', $question->condition_id)->first();
        if ($question->order_number === 3) {
            $message = 'そうだったんだ！'
                . "\n" . 'アガトンに教えてくれてありがとう！'
                . "\n" . 'また気が向いたらお話聞かせて！';
        } else if ($question->order_number === 4) {
            $message = 'だから' . Condition::CONDITION_TYPE[$condition->evaluation] . 'だったんだ！'
                . "\n" . 'アガトンに教えてくれてありがとう！'
                . "\n" . 'また気が向いたらお話聞かせて！';
        }

        $text_message_builder = new TextMessageBuilder($message);
        $question->update([
            'condition_id' => null,
            'feeling_id' => null,
            'operation_type' => null,
            'order_number' => null
        ]);
        return $text_message_builder;
    }
}
