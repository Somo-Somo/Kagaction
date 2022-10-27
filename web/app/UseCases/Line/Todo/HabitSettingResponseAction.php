<?php

namespace App\UseCases\Line\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Models\Habit;
use App\Models\LineUsersQuestion;
use App\Repositories\Todo\TodoRepositoryInterface;
use App\Repositories\Date\DateRepositoryInterface;
use App\Repositories\Line\LineBotRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;

class HabitSettingResponseAction
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
     * @param App\Repositories\Line\LineBotRepositoryInterface
     */
    protected $line_bot_repository;

    /**
     * @param App\Repositories\Todo\TodoRepositoryInterface
     */
    protected $todo_repository;

    /**
     * @param App\Repositories\Date\DateRepositoryInterface
     */
    protected $date_repository;

    /**
     * @param App\Repositories\Line\LineRepositoryInterface $line_repository_interface
     * @param App\Repositories\Todo\TodoRepositoryInterface $todo_repository_interface
     * @param App\Repositories\Date\DateRepositoryInterface $date_repository_interface
     */
    public function __construct(
        LineBotRepositoryInterface $line_bot_repository_interface,
        TodoRepositoryInterface $todo_repository_interface,
        DateRepositoryInterface $date_repository_interface,
    ) {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
        $this->line_bot_repository = $line_bot_repository_interface;
        $this->todo_repository = $todo_repository_interface;
        $this->date_repository = $date_repository_interface;
    }

    /**
     * 受け取ったメッセージを場合分けしていく
     *
     * @param object $event
     * @param User $line_user
     * @param string $action_type
     * @param string $uuid_value
     * @return
     */
    public function invoke(object $event, User $line_user, string $action_type, string $postback_value)
    {
        [$todo_uuid, $frequency_str, $day_str] = explode(",", $postback_value);
        $frequency = (int)$frequency_str;
        $day = (int)$day_str;
        $todo = Todo::where('uuid', $todo_uuid)->first();
        if (!$day && $frequency === Habit::FREQUENCY['毎週']) {
            $multi_message_builder = Habit::selectDayOfWeek($todo, $frequency);
            $this->bot->replyMessage($event->getReplyToken(), $multi_message_builder);
        } else if (!$day && $frequency === Habit::FREQUENCY['毎月']) {
            $multi_message_builder = Habit::selectDayOfMonth($todo, $frequency);
            $this->bot->replyMessage($event->getReplyToken(), $multi_message_builder);
        } else {
            $carousel = Todo::createWhatToDoAfterAddingTodoCarousel($todo, $line_user);
            $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $builder->add(new TextMessageBuilder(Habit::confirmHabit($todo, $frequency, $day)));
            $builder->add(new TemplateMessageBuilder('選択', $carousel));
            $this->bot->replyMessage($event->getReplyToken(), $builder);
            // データの更新
            Habit::updateOrCreate(
                ['todo_uuid' => $todo_uuid],
                [
                    'user_uuid' => $line_user->uuid,
                    'interval' => $frequency,
                    'day' => $day
                ]
            );

            $carbon = Carbon::now();
            if ($frequency === Habit::FREQUENCY['毎日']) {
                $todo_date = $carbon->copy()->format('Y-m-d');
            } else if ($frequency === Habit::FREQUENCY['毎週']) {
                if ($frequency === $carbon->copy()->format('w')) {
                    $todo_date = $carbon->copy()->format('Y-m-d');
                } else {
                    $todo_date = $carbon->copy()->next(Habit::DAY_OF_WEEK_CARBON[$day])->format('Y-m-d');
                }
            } else if ($frequency === Habit::FREQUENCY['毎月']) {
                $this_month_date = $carbon->copy()->setDate($carbon->year, $carbon->month, $day);
                $todo_date = $carbon->lt($this_month_date) ? $this_month_date->format('Y-m-d') : $this_month_date->addMonthsNoOverflow()->format('Y-m-d');
            } else if ($frequency === Habit::FREQUENCY['平日']) {
                $todo_date = $carbon->isWeekday() ?
                    $carbon->copy()->format('Y-m-d') : $carbon->copy()->next(Carbon::MONDAY)->format('Y-m-d');
            } else if ($frequency === Habit::FREQUENCY['週末']) {
                $todo_date = $carbon->isWeekend() ?
                    $carbon->copy()->format('Y-m-d') : $carbon->copy()->next(Carbon::SATURDAY)->format('Y-m-d');
            }
            Todo::where('uuid', $todo_uuid)->update(['date' => $todo_date]);
            // 質問の更新
            $line_user->question->update([
                'question_number' => LineUsersQuestion::NO_QUESTION,
                'parent_uuid' => null
            ]);
            $create_habit = [
                'uuid' => $todo_uuid,
                'user_uuid' => $line_user->uuid,
                'date' => $todo_date,
                'interval' => $frequency,
                'habit_day_no' => $day,
            ];
            $this->todo_repository->updateHabit($create_habit);
            $this->date_repository->updateDate($create_habit);
        }
        return;
    }
}
