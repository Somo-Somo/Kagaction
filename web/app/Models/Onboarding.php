<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

class Onboarding extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['user_uuid'];

    /**
     * Todo追加の呼びかけ
     *
     * @return TemplateMessageBuilder
     */
    public static function callForAdditionalTodo()
    {
        $text =   '「やること一覧」からゴール達成のためにやることを追加していくことができます！' . "\n" . 'どんどん追加していきましょう！';
        return new TemplateMessageBuilder(
            '「やること一覧」からゴール達成のためのやることを追加していくことができます！', // チャット一覧に表示される
            new ButtonTemplateBuilder(
                'ゴールを達成するためにやることを追加してみよう!', // title
                $text, // text
                null, // 画像url
                [
                    new PostbackTemplateActionBuilder(
                        'やること一覧を見る',
                        'action=ALL_TODO_LIST&project_uuid=',
                        null,
                        'openRichMenu',
                        null
                    )
                ]
            )
        );
    }

    /**
     * 振り返りの設定の説明
     *
     * @return string $reply_message
     */
    public static function explainSettingOfCheck()
    {
        return  'また週に1回、もくサポくんから振り返りのリマインドを送ることができるよ！' . "\n" . '「メニュー>振り返り」から日時の設定を自分で変更することができます！';
    }
}
