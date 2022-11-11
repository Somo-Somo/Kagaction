<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class RunningHerokuAndNeo4j extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:running-heroku-and-neo4j';

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
    public function __construct(UserRepositoryInterface $user_repository_interface)
    {
        parent::__construct();
        $this->user_repository = $user_repository_interface;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->user_repository->getUserHasProjetAndTodo(env('TEST_USER_UUID'));
        User::where('uuid', env('TEST_USER_UUID'))->first();
        return;
    }
}
