<?php

namespace App\Models;

use App\Services\CarouselContainerBuilder\SelectInTalkCarouselContainerBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
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
     * 最初の挨拶
     *
     * @return
     */
    public static function firstGreeting()
    {
        return  [
            new TextMessageBuilder('はじめまして。アガトンです！' . "\n" . 'これからよろしくお願いします🙇‍♂️'),
            new TextMessageBuilder('あなたのことを何とお呼びしたらいいですか？' . "\n" . 'ニックネームを教えてください！'),
        ];
    }

    /**
     * 最初の挨拶の後のAgathon説明
     *
     * @return string $first_greeting
     */
    public static function explainAboutAgathon(string $nickname)
    {
        return  [
            new TextMessageBuilder($nickname . 'とお呼びしたら良いのですね！' . "\n" . '答えてくれてありがとうございます🙇‍♂️'),
            new TextMessageBuilder('このアガトンではアガトンとの会話形式で簡単にその日の自分の調子や感情を記録することができます！'),
        ];
    }

    /**
     * 体験してみよう
     *
     * @return string $first_greeting
     */
    public static function letsDemoSelfCheck()
    {
        return  [
            new TextMessageBuilder('そしてアガトンとの会話を通して現在の自分を客観的に見つめることができます。'),
            new TextMessageBuilder('どのような感じで行っていくのか実際に体験してみましょう！'),
        ];
    }

    /**
     * 今の調子は？（体験版）
     *
     * @return string $first_greeting
     */
    public static function askConditionInDemo(string $nickname)
    {
        $ask_message = $nickname . 'の今の調子はどうですか？';
        $carousel_container = SelectInTalkCarouselContainerBuilder::createSelectInTalkBubbleContainer(Condition::CAROUSEL);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($ask_message));
        $multi_message->add(new FlexMessageBuilder($ask_message, $carousel_container));
        return  $multi_message;
    }
}
