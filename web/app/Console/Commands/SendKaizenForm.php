<?php

namespace App\Console\Commands;

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
     * @param App\Repositories\User\UserRepositoryInterface
     */
    protected $user_repository;

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

        return;
    }
}
