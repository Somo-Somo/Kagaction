<?php

namespace App\Console\Commands;

use App\UseCases\Line\Habit\UpdateHabitDate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class UpdateHabitDateBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:update-habit-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('update-habit-date');
        $update_habit_date = new UpdateHabitDate();
        $update_habit_date->invoke();
        return 0;
    }
}
