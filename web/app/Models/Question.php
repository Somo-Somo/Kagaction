<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

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
     * 起こったことを書いてもらうようお願いする
     *
     * @param Question $question
     * @return
     */
    public static function pleaseWriteWhatHappened(Question $question)
    {
        $question->update(['order_number' => 3]);
        $message = 'どんなことがあったの？？' . "\n" . 'アガトンにも教えて！';
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
        $question->update(['order_number' => 3]);
        $condition = Condition::where('id', $question->condition_id)->first();
        $message = 'そうなんだ。' . "\n" . 'そしたらどうして' . $user->name . 'さんは今' . Condition::CONDITION_TYPE[$condition->evaluation] . 'なの？';
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
        $message = 'だから' . Condition::CONDITION_TYPE[$condition->evaluation] . 'だったんだ！'
            . "\n" . 'アガトンに教えてくれてありがとう！'
            . "\n" . 'また何かあったらお話聞かせて！';
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
