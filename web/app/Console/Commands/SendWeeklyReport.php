<?php

namespace App\Console\Commands;

use App\UseCases\Line\WeeklyReportNotificationAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;


class SendWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-weekly-report';

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
        Log::info('send-weekly-report');
        if (intval(date('w')) === 0) {
            $weekly_report_notification = new WeeklyReportNotificationAction();
            $weekly_report_notification->invoke();
        }
        return 0;
    }
}
