<?php

namespace App\Console\Commands;

use App\UseCases\Line\Todo\Notification\NotifyTodoCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LineBotScheduleBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:line-bot-schedule';

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
        Log::info('start');
        $notify_todo_check = new NotifyTodoCheck();
        $notify_todo_check->invoke();
        return 0;
    }
}
