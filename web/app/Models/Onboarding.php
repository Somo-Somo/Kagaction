<?php

namespace App\Models;

use App\Services\CarouselContainerBuilder\SelectInTalkCarouselContainerBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use phpDocumentor\Reflection\Types\Boolean;

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
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function firstGreeting()
    {
        $multi_message = new MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder('はじめまして。アガトンです！' . "\n" . 'これからよろしくお願いします！！🙇‍♂️'));
        $multi_message->add(new TextMessageBuilder('何とお呼びしたらいいですか？' . "\n" . '呼んでもよいニックネームを教えてください！！'));
        return $multi_message;
    }

    /**
     * 最初の挨拶の後のAgathon説明
     *
     * @param string $nickname
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function explainAboutAgathon(string $nickname)
    {
        $multi_message = new MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($nickname . 'とお呼びしたら良いのですね！' . "\n" . '答えてくれてありがとうございます🙇‍♂️'));
        $multi_message->add(new TextMessageBuilder(
            'このアガトンではチャットでの会話を通して、その日の自分の調子や感情を簡単に記録することができます。',
            new QuickReplyMessageBuilder([
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('なるほど', 'なるほど'))
            ])
        ));
        return $multi_message;
    }

    /**
     * 体験してみよう
     *
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function letsDemoSelfCheck()
    {
        $multi_message = new MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder(
            'どのような形で会話して記録していくのか実際に体験してみましょう！',
            new QuickReplyMessageBuilder([
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('OK！', 'OK！'))
            ])
        ));
        return $multi_message;
    }

    /**
     * 今の調子は？（体験版）
     *
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function askConditionInDemo(string $nickname)
    {
        $ask_message = $nickname . 'さんの今の調子はどうですか？' . "\n" . 'この5つの中からタップしてお選びください！';
        $carousel_container = SelectInTalkCarouselContainerBuilder::createSelectInTalkBubbleContainer(Condition::CAROUSEL);
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($ask_message));
        $multi_message->add(new FlexMessageBuilder($ask_message, $carousel_container));
        return  $multi_message;
    }

    /**
     * explain
     *
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function explainDiffrent()
    {
        $explain_message = 'また「話す」を押すと「今の調子や気持ちについて話す」と「今日の振り返りをする」の２種類のボタンが表示されます。';
        $about_diffrent = '「今の調子や気持ちについて話す」はその時々のことを記録することができて、「今日の振り返りをする」は1日のことをまとめて記録することができます！';
        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder($explain_message));
        $multi_message->add(new TextMessageBuilder(
            $about_diffrent
        ));
        $image_url = config('app.env') === 'production' ?
            config('app.mix_firebase_access_url') . '/o/onboarding%2Fselect_talk_about.jpg?alt=media&token=32ee6db3-bd06-4c8d-ad1e-c0b1bcbdacfb' :
            config('app.mix_firebase_access_url') . '/o/onboarding%2Fselect_talk_about.jpg?alt=media&token=6f6e30a4-fb72-4ed9-a10b-29224dd229bf';
        $multi_message->add(new ImageMessageBuilder(
            $image_url,
            $image_url,
            new QuickReplyMessageBuilder([
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('なるほど！', 'なるほど！'))
            ])
        ));
        return  $multi_message;
    }

    /**
     * explain
     *
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function explainWeeklyReport()
    {
        $image_url = config('app.env') === 'production' ?
            config('app.mix_firebase_access_url') . '/o/onboarding%2Fweekly_report.png?alt=media&token=22249168-826f-4b66-95c6-a996251bfcf9' :
            config('app.mix_firebase_access_url') . '/o/onboarding%2Fweekly_report.png?alt=media&token=293fcb05-a919-4ce4-a7fe-9d41c7c05385';

        $multi_message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder('また、毎週日曜日に記録した調子や感情をもとに生成した画像を週のレポートとして送信します。'));
        $multi_message->add(new ImageMessageBuilder(
            $image_url,
            $image_url,
            new QuickReplyMessageBuilder([
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('了解！', '了解！'))
            ])
        ));
        return  $multi_message;
    }

    /**
     * 今の調子は？（体験版）
     *
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function suggestSelfCheckNotification()
    {
        $multi_message = new MultiMessageBuilder();
        $multi_message->add(new TextMessageBuilder('記録をしていくことを忘れないために毎日指定した時間にアガトンからLINEで通知を送ることができます！'));
        $multi_message->add(new TextMessageBuilder(
            '通知の設定を行いますか？',
            new QuickReplyMessageBuilder([
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('はい', 'はい')),
                new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('いいえ', 'いいえ')),
            ])
        ));
        return $multi_message;
    }

    /**
     * 今の調子は？（体験版）
     * @param Bollean $is_setting_self_check_notification
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
     */
    public static function suggestWeeklyReportNotification(Boolean $is_setting_self_check_notification)
    {
        $multi_message = new MultiMessageBuilder();
        if ($is_setting_self_check_notification) {
            # code...
        } else {
            $multi_message->add(new TextMessageBuilder('かしこまりました！'));
            $multi_message->add(new TextMessageBuilder(
                '毎週日曜日の朝に前の週の日曜日から土曜日までの調子と感情を色で表したグラフの画像が生成されます。'
                    . "\n" . 'こちらは通知してもよろしいですか？'
            ));
        }

        $multi_message->add(new TextMessageBuilder('記録するのを忘れないためにアガトンからLINEで通知を送ることができます！'));
        $multi_message->add(new TextMessageBuilder('通知の設定を行いますか？'));
        return $multi_message;
    }
}
