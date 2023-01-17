<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Condition;
use App\Models\Feeling;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(3)->create()->each(function ($user) {
            Condition::factory(3)->create(['user_uuid' => $user->uuid])->each(
                function ($condition) {
                    Feeling::factory(1)->create(['user_uuid' => $condition->user_uuid, 'condition_id' => $condition->id]);
                }
            );
        });
    }
}
