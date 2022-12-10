<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Diary extends Model
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
        'detail',
        'created_at'
    ];

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
}
