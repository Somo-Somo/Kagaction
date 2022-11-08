<?php

namespace App\UseCases\Line\Habit;

use App\Models\User;
use App\Models\Todo;
use App\Models\Habit;
use Carbon\Carbon;
use App\Repositories\Date\DateRepositoryInterface;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use DateTime;

class UpdateHabitDate
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
     * @param App\Repositories\Date\DateRepositoryInterface
     */
    protected $date_repository;

    /**
     * @param App\Repositories\Date\DateRepositoryInterface $date_repository_interface
     */
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     * Todoの名前を変更する
     *
     * @param object $event
     * @param User $line_user
     * @param string $todo_uuid
     * @return
     */
    public function invoke()
    {
        $today = Carbon::now();
        $yesterday = $today->copy()->subDay();
        $yesterdays_day_of_week = $yesterday->copy()->format('w');
        $yesterday_day = $yesterday->copy()->format('d');
        $array = [
            'day_of_week' => $yesterdays_day_of_week,
            'day' => $yesterday_day,
            'is_last_of_month' => $yesterday->isLastOfMonth(),
            'weekday' => $yesterday->isWeekDay(),
            'weekend' => $yesterday->isWeekend(),
        ];
        # 毎日, 毎週で昨日の人, 毎月で昨日の人, 平日が昨日、休日が昨日
        $todos = Todo::where('date', $yesterday->copy()->format('Y-m-d'))
            ->whereIn('uuid', function ($query) use ($array) {
                $query->from('habits')
                    ->select('todo_uuid')
                    ->orWhere(function ($query) {
                        $query->where('interval', 0);
                    })
                    ->orWhere(function ($query) use ($array) {
                        $query->where('interval', 1)->where('day', $array['day_of_week']);
                    })
                    ->orWhere(function ($query) use ($array) {
                        $query->where('interval', 2)->where('day', $array['day']);
                    })
                    ->orWhere(function ($query) use ($array) {
                        if ($array['is_last_of_month']) {
                            $query->where('interval', 2)->where('day', 32);
                        }
                    })
                    ->orWhere(function ($query) use ($array) {
                        if ($array['weekday']) {
                            $query->where('interval', 3);
                        }
                    })
                    ->orWhere(function ($query) use ($array) {
                        if ($array['weekend']) {
                            $query->where('interval', 4);
                        }
                    });
            })->get();

        $update_todos_key_and_date = [];
        foreach ($todos as $todo) {
            if ($todo->habit->first()->interval === Habit::FREQUENCY['毎日']) {
                $date = $yesterday->copy()->addDay()->format('Y-m-d');
            } else if ($todo->habit->first()->interval === Habit::FREQUENCY['毎週']) {
                $date = $yesterday->copy()->addDay(7)->format('Y-m-d');
            } else if ($todo->habit->first()->interval === Habit::FREQUENCY['毎月']) {
                $date = $yesterday->copy()->addMonthNoOverflow();
            } else if ($todo->habit->first()->interval === Habit::FREQUENCY['平日']) {
                $date = $yesterday->isWeekday() ? $yesterday->copy()->format('Y-m-d') : $yesterday->copy()->next(Carbon::MONDAY)->format('Y-m-d');
            } else if ($todo->habit->first()->interval === Habit::FREQUENCY['週末']) {
                $date = $yesterday->isWeekend() ?
                    $yesterday->copy()->format('Y-m-d') : $yesterday->copy()->next(Carbon::SATURDAY)->format('Y-m-d');
            } else {
                $date = null;
            }
            $update_todos_key_and_date[] = [
                'uuid' => $todo->uuid,
                'name' => $todo->name,
                'user_uuid' => $todo->user_uuid,
                'project_uuid' => $todo->project_uuid,
                'parent_uuid' => $todo->parent_uuid,
                'date' => $date,
                'depth' => $todo->depth
            ];
        }

        Todo::upsert($update_todos_key_and_date, ['uuid'], ['date']);

        return;
    }
}
