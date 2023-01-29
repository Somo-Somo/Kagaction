<?php

namespace App\Console\Commands;

use App\UseCases\Line\SendKaizenFormAction;
use Illuminate\Console\Command;

class SendKaizenForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-kaizen-form';

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
        $send_kaizen_form = new SendKaizenFormAction();
        $send_kaizen_form->invoke();
        return 0;
    }
}
