<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHabitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid', 255);
            $table->string('todo_uuid', 255)->unique();
            $table->integer('interval');
            $table->integer('day')->nullable();
            $table->integer('consecutive_days')->default(0);
            $table->timestamps();
            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('todo_uuid')->references('uuid')->on('todos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('habits');
    }
}
