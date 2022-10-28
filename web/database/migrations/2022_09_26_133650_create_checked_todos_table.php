<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckedTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checked_todos', function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid', 255);
            $table->string('todo_uuid', 255);
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
        Schema::dropIfExists('checked_todos');
    }
}
