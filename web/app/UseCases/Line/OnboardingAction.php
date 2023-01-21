<?php

namespace App\UseCases\Line;

use App\Models\Onboarding;
use App\Models\Condition;
use App\Models\Diary;
use App\Models\Feeling;
use App\Models\Question;
use App\Models\SelfCheckNotification;
use App\Models\User;
use App\Models\WeeklyReportNotification;
use App\Services\CarouselContainerBuilder\PageTransitionBtnCarouselContainerBuilder;
use App\Services\CarouselContainerBuilder\TalkLogCarouselContainerBuilder;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class OnboardingAction
{
    /**
     * @param LINE\LINEBot\HTTPClient\CurlHTTPClient
     */
    protected $httpClient;

    /**
     * @param LINE\LINEBot
     */
    protected $bot;

    /**
     */
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     * 該当するユーザーに振り返りの通知を送る
     *
     * @param User $user
     * @param Question $question
     * @return
     */
    public function invoke($user, $question, $event)
    {
        if ($question->order_number === 1) {
            $user->update(['nickname' => $event->getText()]);
            $this->bot->replyMessage(
                $event->getReplyToken(),
                Onboarding::explainAboutAgathon($user->nickname)
            );
            $question->update(['order_number' => 2]);
        } elseif ($question->order_number === 2) {
            $this->bot->replyMessage(
                $event->getReplyToken(),
                Onboarding::letsDemoSelfCheck()
            );
            $question->update(['order_number' => 3]);
        } elseif ($question->order_number === 3) {
            $this->bot->replyMessage(
                $event->getReplyToken(),
                Onboarding::askConditionInDemo($user->nickname)
            );
        } elseif ($question->order_number === 4) {
            $this->bot->replyMessage($event->getReplyToken(), Question::askAboutFeeling($question));
            Diary::create([
                'user_uuid' => $user->uuid,
                'condition_id' => $question->condition_id,
                'detail' => $event->getText()
            ]);
            $question->update(['order_number' => 5]);
        } elseif ($question->order_number === 5) {
            $feeling = Feeling::create([
                'user_uuid' => $user->uuid,
                'condition_id' => $question->condition->id,
                'feeling_type' => Feeling::JA_EN[$event->getText()],
                'date' => $question->condition->date,
                'time' => $question->condition->time
            ]);
            $this->bot->replyMessage($event->getReplyToken(),  Question::questionAfterAskAboutFeeling($user, $feeling, $question));
            $question->update(['order_number' => 6, 'feeling_id' => $feeling->id]);
        } else if ($question->order_number === 6) {
            $diary = Diary::where('user_uuid', $user->uuid)
                ->where('condition_id', $question->condition->id)
                ->first();
            $diary->update(['detail' => $diary->detail . "\n" . $event->getText()]);
            $this->bot->replyMessage($event->getReplyToken(), Question::thanksMessage($question, $event->getText(), $user));
            $question->update([
                'order_number' => 7, 'condition_id' => null, 'feeling_id' => null
            ]);
        } else if ($question->order_number === 7) {
            $this->bot->replyMessage($event->getReplyToken(), Onboarding::explainDiffrent());
            $question->update(['order_number' => 8]);
        } else if ($question->order_number === 8) {
            $this->bot->replyMessage($event->getReplyToken(), Onboarding::explainWeeklyReport());
            $question->update(['order_number' => 9]);
        } else if ($question->order_number === 9) {
            WeeklyReportNotification::create([
                'user_uuid' => $user->uuid,
                'time' => '09:00:00'
            ]);
            $this->bot->replyMessage($event->getReplyToken(), Onboarding::suggestSelfCheckNotification());
            $question->update(['order_number' => 10]);
        } else if ($question->order_number === 10) {
            if ($event->getText() === 'はい') {
                $flex_message = SelfCheckNotification::selectDateTimeFlexMessageBuilder(
                    [
                        SelfCheckNotification::createSettingTimeMessageBuilder('AM'),
                        SelfCheckNotification::createSettingTimeMessageBuilder('PM'),
                    ]
                );
                $multi_message = new MultiMessageBuilder();
                $multi_message->add(new TextMessageBuilder('通知をしてほしい時間を選択してください！'));
                $multi_message->add($flex_message);
                $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                return;
            } else if ($event->getText() === 'いいえ') {
                $multi_message = new MultiMessageBuilder();
                $multi_message->add(new TextMessageBuilder('了解しました！'));
                $multi_message->add(new TextMessageBuilder(
                    '通知の設定を変更したい場合は、メニューの「設定」->「通知の設定」から変更することができます！',
                    new QuickReplyMessageBuilder([
                        new QuickReplyButtonBuilder(new MessageTemplateActionBuilder('了解！', '了解！'))
                    ])
                ));
                $this->bot->replyMessage($event->getReplyToken(), $multi_message);
                $question->update(['order_number' => 11]);
            }
        } else if ($question->order_number === 11) {
            $multi_message = new MultiMessageBuilder();
            $multi_message->add(new TextMessageBuilder('これで説明は終わります！' . "\n" . '問題が発生した場合やここを改善してほしいという要望がある場合は「設定」->「お問い合わせ」からご連絡ください！'));
            $this->bot->replyMessage($event->getReplyToken(), $multi_message);
            $question->update(['operation_type' => null, 'order_number' => null]);
        }
    }
}
