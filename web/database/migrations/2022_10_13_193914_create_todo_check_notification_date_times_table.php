<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodoCheckNotificationDateTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo_check_notification_date_times', function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid', 255);
            $table->integer('notification_date');
            $table->time('notification_time');
            $table->timestamps();
            $table->foreign('user_uuid')->references('uuid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todo_check_notification_date_times');
    }
}
