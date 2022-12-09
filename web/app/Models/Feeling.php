<?php

namespace App\Models;

use GuzzleHttp\Psr7\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('ない', 'ない')),
        ];
    }
}
